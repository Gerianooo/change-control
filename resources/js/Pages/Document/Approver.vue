<script setup>
import { getCurrentInstance, nextTick, onMounted, onUnmounted, onUpdated, ref } from 'vue'
import { useForm, Link } from '@inertiajs/inertia-vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Select from '@vueform/multiselect'
import Icon from '@/Components/Icon.vue'
import Swal from 'sweetalert2'
import { Inertia } from '@inertiajs/inertia'

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

const open = ref(false)
const show = () => {
  open.value = true
  nextTick(() => self.refs.user?.focus())
}

const close = () => {
  open.value = false
}

const store = () => {
  return form.post(route('document.approver.add', {
    document: document.id,
    user: form.user
  }), {
    onSuccess: () => close() || form.reset(),
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

  response.isConfirmed && Inertia.delete(route('document.approver.detach', approver.id))
}

const up = approver => Inertia.patch(route('approver.up', approver.id))
const down = approver => Inertia.patch(route('approver.down', approver.id))

const rounded = () => {
  const { wrapper } = self.refs

  wrapper && Array.isArray(wrapper) && wrapper.forEach(parent => {
    parent.childNodes && Object.values(parent.childNodes).forEach((child, i) => {
      i === 0 && child.classList?.add('rounded-l-md')
      i + 1 === parent.childNodes.length && child.classList?.add('rounded-r-md')
    })
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
    <div class="flex flex-col bg-white rounded-md">
      <div class="flex items-center space-x-2 bg-slate-200 p-2 rounded-t-md">
        <Link :href="route('document.index')" class="bg-slate-600 hover:bg-slate-700 rounded-md px-3 py-1 text-sm text-white transition-all">
          <div class="flex items-center space-x-1">
            <Icon name="caret-left" />
            <p class="uppercase font-semibold">{{ __('back') }}</p>
          </div>
        </Link>

        <button @click.prevent="show" class="bg-green-600 hover:bg-green-700 rounded-md px-3 py-1 text-sm text-white transition-all">
          <div class="flex items-center space-x-1">
            <Icon name="plus" />
            <p class="uppercase font-semibold">{{ __('add') }}</p>
          </div>
        </button>
      </div>

      <div class="flex flex-col space-y-2 p-4">
        <div v-for="(approver, i) in approvers" :key="i" class="flex items-center justify-between bg-slate-200 hover:bg-slate-100 rounded-md px-4 py-2">
          <div class="flex items-center space-x-1">
            <p>{{ approver.position }}</p>
            <p class="uppercase">{{ approver.user.name }}</p>
          </div>

          <div ref="wrapper" class="flex items-center">
            <Icon @click.prevent="up(approver)" v-if="approver.position > 1" name="arrow-up" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 transition-all cursor-pointer" />
            <Icon @click.prevent="down(approver)" v-if="approver.position !== approvers.length" name="arrow-down" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 transition-all cursor-pointer" />
            <Icon @click.prevent="edit(approver)" name="edit" class="px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white transition-all cursor-pointer" />
            <Icon @click.prevent="detach(approver)" name="trash" class="px-2 py-1 bg-red-600 hover:bg-red-600 text-white transition-all cursor-pointer" />
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>

  <transition name="fade">
    <div v-if="open" class="fixed top-0 left-0 w-full h-screen flex items-center justify-center bg-black bg-opacity-40">
      <form @submit.prevent="submit" class="w-full max-w-xl rounded-md">
        <div class="flex flex-col bg-white rounded-md">
          <div class="flex items-center justify-end space-x-1 bg-slate-200 rounded-t-md p-2">
            <Icon @click.prevent="close" name="times" class="px-2 py-1 rounded-md bg-red-500 hover:bg-red-600 transition-all cursor-pointer text-white" />
          </div>

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
                  class="uppercase"
                  required />
              </div>
            </div>
          </div>

          <div class="flex items-center space-x-2 justify-end bg-slate-200 rounded-b-md px-2 py-1">
            <button type="submit" class="bg-slate-600 hover:bg-slate-700 rounded-md px-3 py-1 text-white text-sm transition-all">
              <div class="flex items-center space-x-1">
                <Icon name="check" />
                <p class="uppercase font-semibold">{{ form.id ? 'update' : 'add' }}</p>
              </div>
            </button>
          </div>
        </div>
      </form>
    </div>
  </transition>
</template>