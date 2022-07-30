<script setup>
import { getCurrentInstance, nextTick, onMounted, onUnmounted, onUpdated, ref } from 'vue'
import { useForm, Link } from '@inertiajs/inertia-vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Select from '@vueform/multiselect'
import Card from '@/Components/Card.vue'
import Icon from '@/Components/Icon.vue'
import Swal from 'sweetalert2'
import { Inertia } from '@inertiajs/inertia'
import Draggable from 'vuedraggable'
import Modal from '@/Components/Modal.vue'
import Close from '@/Components/Button/Close.vue'
import ButtonGreen from '@/Components/Button/Green.vue'
import ButtonBlue from '@/Components/Button/Blue.vue'
import ButtonRed from '@/Components/Button/Red.vue'
import ButtonDark from '@/Components/Button/Dark.vue'

const self = getCurrentInstance()
const { approvers, document, users } = defineProps({
  approvers: Array,
  document: Object,
  users: Array,
})

const form = useForm({
  id: null,
  user: null,
})

const options = {
  animation: 200,
  group: "description",
  disabled: false,
  ghostClass: "ghost"
}

const lists = ref([...approvers])
const drag = ref(false)

const open = ref(false)
const show = () => {
  open.value = true
  nextTick(() => {
    self.refs.user?.focus()
    self.refs.user?.close()
  })
}

const close = () => {
  open.value = false
}

const store = () => {
  return form.post(route('document.approver.add', {
    document: document.id,
    user: form.user
  }), {
    onSuccess: () => {
      Inertia.get(route(route().current(), document.id))
    },
    onError: () => show(),
  })
}

const edit = approver => {
  form.id = approver.id
  form.user = approver.user.id

  show()
}

const update = () => {
  return form.patch(route('document.approver.update', {
    approver: form.id,
    user: form.user,
  }), {
    onSuccess: () => close() || form.reset(),
    onError: () => show(),
  })
}

const submit = () => form.id ? update() : store()

const detach = async approver => {
  const response = await Swal.fire({
    title: __('are you sure want to remove it'),
    text: __('you can add them it later, but you can\'t recovery it'),
    icon: 'question',
    showCancelButton: true,
    showCloseButton: true,
  })

  response.isConfirmed && Inertia.delete(route('document.approver.detach', approver.id), {
    onSuccess: () => {
      Inertia.get(route(route().current(), document.id))
    },
  })
}

const rounded = () => {
  const { wrapper } = self.refs

  wrapper && Array.isArray(wrapper) && wrapper.forEach(parent => {
    parent.childNodes && Object.values(parent.childNodes).forEach((child, i) => {
      i === 0 && child.classList?.add('rounded-l-md')
      i + 1 === parent.childNodes.length && child.classList?.add('rounded-r-md')
    })
  })
}

const change = () => {
  Swal.showLoading()

  return useForm({
    approvers: lists.value,
  }).patch(route('document.approver.save', document.id), {
    onSuccess: () => {
      Inertia.get(route(route().current(), document.id))
      Swal.close()
    },
  })
}

const esc = e => e.key === 'Escape' && close()

onMounted(() => window.addEventListener('keydown', esc))
onUnmounted(() => window.removeEventListener('keydown', esc))
onMounted(rounded)
onUpdated(rounded)
</script>

<style src="@vueform/multiselect/themes/default.css"></style>

<template>
  <DashboardLayout title="Document Approver">
    <Card class="flex flex-col bg-white dark:bg-gray-700 dark:text-gray-200 rounded-md">
      <template #header>
        <div class="flex items-center space-x-2 bg-slate-200 dark:bg-gray-800 p-2 rounded-t-md">
          <Link :href="route('document.index')">
            <ButtonDark class="bg-gray-700">
              <Icon name="caret-left" />
              <p class="uppercase font-semibold">{{ __('back') }}</p>
            </ButtonDark>
          </Link>

          <ButtonGreen @click.prevent="show">
            <Icon name="plus" />
            <p class="uppercase font-semibold">{{ __('add') }}</p>
          </ButtonGreen>
        </div>
      </template>

      <template #body>
        <div class="flex flex-col space-y-2 p-4 w-2/3">
          <Draggable
            tag="div"
            v-model="lists"
            v-bind="options"
            @start="drag = true"
            @end="drag = false"
            item-key="position"
            @change="change"
          >
            <template #item="{ element }">
              <div class="list-group-item dark:bg-gray-800 m-1 px-4 py-2 rounded-md uppercase">
                <div class="flex items-center space-x-2 justify-between">
                  <p>
                    {{ element.position }}. {{ element.user.name }}
                  </p>

                  <div class="flex-none">
                    <Icon @click.prevent="edit(element)" name="edit" class="px-2 py-1 rounded-l-md bg-blue-600 hover:bg-blue-700 text-sm text-white transition-all" />
                    <Icon @click.prevent="detach(element)" name="trash" class="px-2 py-1 rounded-r-md bg-red-600 hover:bg-red-700 text-sm text-white transition-all" />
                  </div>
                </div>
              </div>
            </template>
          </Draggable>
        </div>
      </template>
    </Card>
  </DashboardLayout>

  <Modal :show="open">
    <form @submit.prevent="submit" class="w-full max-w-xl rounded-md">
      <Card class="flex flex-col bg-white dark:bg-gray-700 dark:text-gray-200 rounded-md">
        <template #header>
          <div class="flex items-center justify-end space-x-1 bg-slate-200 dark:bg-gray-800 rounded-t-md p-2">
            <Close @click.prevent="close" />
          </div>
        </template>

        <template #body>
          <div class="flex flex-col space-y-2 p-4">
            <div class="flex items-center space-x-2">
              <label for="user" class="lowercase first-letter:capitalize flex-none w-1/4">{{ __('user') }}</label>
              <div class="w-full">
                <Select
                  v-model="form.user"
                  ref="user"
                  :options="users.map(u => ({
                    label: u.name,
                    value: u.id,
                  }))"
                  :searchable="true"
                  class="uppercase text-gray-800"
                  required />
              </div>
            </div>
          </div>
        </template>

        <template #footer>
          <div class="flex items-center space-x-2 justify-end bg-slate-200 dark:bg-gray-800 rounded-b-md px-2 py-1">
            <ButtonGreen type="submit">
              <div class="flex items-center space-x-1">
                <Icon name="check" />
                <p class="uppercase font-semibold">{{ form.id ? 'update' : 'add' }}</p>
              </div>
            </ButtonGreen>
          </div>
        </template>
      </Card>
    </form>
  </Modal>
</template>

<style>
.button {
  margin-top: 35px;
}
.flip-list-move {
  transition: transform 0.5s;
}
.no-move {
  transition: transform 0s;
}
.ghost {
  opacity: 0.5;
  background: #c8ebfb;
}
.list-group {
  min-height: 20px;
}
.list-group-item {
  cursor: move;
}
.list-group-item i {
  cursor: pointer;
}
</style>