<script setup>
import { getCurrentInstance, nextTick, onMounted, onUnmounted, ref } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import { Link, useForm } from '@inertiajs/inertia-vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Card from '@/Components/Card.vue'
import Icon from '@/Components/Icon.vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import Nested from './Nested.vue'
import Modal from '@/Components/Modal.vue'
import Close from '@/Components/Button/Close.vue'
import ButtonRed from '@/Components/Button/Red.vue'
import ButtonDark from '@/Components/Button/Dark.vue'
import ButtonBlue from '@/Components/Button/Blue.vue'
import ButtonGreen from '@/Components/Button/Green.vue'

const self = getCurrentInstance()
const timeout = ref(null)
const { revision } = defineProps({
  revision: Object,
})

const open = ref(false)
const form = useForm({
  id: null,
  name: '',
  revision_id: revision.id,
})
const procedures = ref([])

const show = () => {
  open.value = true
  
  nextTick(() => self.refs.name?.focus())
}

const close = () => {
  open.value = false

  fetch()
}

const fetch = async () => {
  try {
    const response = await axios.get(route('api.v1.revision.procedures', revision.id))
    procedures.value = response.data
  } catch (e) {
    const response = await Swal.fire({
      title: 'Are you want to try again?',
      text: `${e}`,
      icon: 'question',
      showCancelButton: true,
      showCloseButton: true,
    })

    response.isConfirmed && fetch()
  }
}

const store = () => {
  return form.post(route('procedure.store'), {
    onSuccess: () => {
      close()
      fetch()
      form.reset()
    },
    onError: () => nextTick(show),
  })
}

const edit = procedure => {
  form.id = procedure.id
  form.name = procedure.name

  show()
}

const update = () => {
  return form.patch(route('procedure.update', form.id), {
    onSuccess: () => {
      close()
      fetch()
      form.reset()
    },
    onError: () => nextTick(show),
  })
}

const save = async () => {
  timeout.value && clearTimeout(timeout.value)
  timeout.value = setTimeout(() => {
    return useForm({procedures: procedures.value}).patch(route('procedure.save', revision.id), {
      onFinish: () => clearTimeout(timeout.value),
    })
  }, 100)
}

const submit = () => form.id ? update() : store()

const esc = e => e.key === 'Escape' && close()

onMounted(fetch)

onMounted(() => window.addEventListener('keydown', esc))
onUnmounted(() => window.removeEventListener('keydown', esc))
</script>

<template>
  <DashboardLayout :title="__('Revision procedures')">
    <Card class="bg-white dark:bg-gray-700 dark:text-gray-200">
      <template #header>
        <div class="flex items-center space-x-1 bg-slate-200 dark:bg-gray-800 p-2">
          <Link :href="route('document.revisions', revision.document_id)">
            <ButtonDark class="bg-gray-700">
              <Icon name="caret-left" />
              <p class="uppercase font-semibold">{{ __('back') }}</p>
            </ButtonDark>
          </Link>

          <ButtonGreen @click.prevent="show">
            <Icon name="plus" />
            <p class="uppercase font-semibold">{{ __('create') }}</p>
          </ButtonGreen>
        </div>
      </template>

      <template #body>
        <div class="flex flex-col space-y-4 p-4">
          <Nested v-if="procedures" :procedures="procedures" :save="save" :edit="edit" />
        </div>
      </template>
    </Card>
  </DashboardLayout>

  <Modal :show="open">
    <form @submit.prevent="submit" class="w-full max-w-xl h-fit shadow-xl">
      <Card class="bg-white dark:bg-gray-700 dark:text-gray-200">
        <template #header>
          <div class="flex items-center space-x-2 justify-end p-2 bg-slate-200 dark:bg-gray-800">
            <Close @click.prevent="close" />
          </div>
        </template>

        <template #body>
          <div class="p-4">
            <div class="flex flex-col space-y-2">
              <div class="flex items-center space-x-2">
                <label for="name" class="lowercase first-letter:capitalize w-1/3">{{ __('name') }}</label>
                <input v-model="form.name" ref="name" type="text" class="w-full bg-transparent rounded-md px-3 py-1 placeholder:capitalize" :placeholder="__('table of content')">
              </div>

              <div v-if="form.errors.name" class="text-sm text-right text-red-500">{{ form.errors.name }}</div>
            </div>
          </div>
        </template>

        <template #footer>
          <div class="flex items-center justify-end space-x-2 px-2 py-1 bg-slate-200 dark:bg-gray-800 rounded-b-md">
            <ButtonGreen type="submit">
              <Icon name="check" />
              <p class="uppercase font-semibold">{{ __(form.id ? 'update' : 'create') }}</p>
            </ButtonGreen>
          </div>
        </template>
      </Card>
    </form>
  </Modal>
</template>