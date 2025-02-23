<script setup>
import { getCurrentInstance, ref, onMounted, onUnmounted, nextTick } from 'vue'
import { useForm, Link } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Card from '@/Components/Card.vue'
import Icon from '@/Components/Icon.vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import Select from '@vueform/multiselect'
import Nested from './Nested.vue'
import Modal from '@/Components/Modal.vue'
import Close from '@/Components/Button/Close.vue'
import ButtonGreen from '@/Components/Button/Green.vue'
import ButtonBlue from '@/Components/Button/Blue.vue'
import Input from '@/Components/Input.vue'
import InputError from '@/Components/InputError.vue'

const self = getCurrentInstance()
const props = defineProps({
  permissions: Array,
  routes: Array,
  icons: Array,
  menus: Array,
})

const menus = ref(props.menus || [])
const search = ref('')
const form = useForm({
  id: null,
  name: '',
  icon: 'circle',
  route_or_url: '',
  actives: [],
  permissions: [],
})

const fetch = async () => {
  try {
    const response = await axios.get(route('api.v1.superuser.menu'))
    menus.value = response.data
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
const icon = ref(false)
const open = ref(false)
const show = () => {
  open.value = true

  nextTick(() => self.refs.name?.focus())
}

const close = () => {
  open.value = false
  form.id && form.reset()
}

const store = () => {
  return form.post(route('superuser.menu.store'), {
    onSuccess: () => {
      close()
      form.reset()
      Inertia.get(route(route().current()))
    },

    onError: () => {
      show()
    },
  })
}

const edit = menu => {
  form.id = menu.id
  form.name = menu.name
  form.icon = menu.icon
  form.route_or_url = menu.route_or_url
  form.actives = menu.actives
  form.permissions = menu.permissions.map(p => p.id)

  nextTick(show)
}

const update = () => {
  return form.patch(route('superuser.menu.update', form.id), {
    onSuccess: () => {
      close()
      form.reset()
      Inertia.get(route(route().current()))
    },

    onError: () => {
      show()
    },
  })
}

const destroy = async menu => {
  const response = await Swal.fire({
    title: 'Are you sure want to delete?',
    text: 'You can\'t recover it after deleted',
    icon: 'question',
    showCloseButton: true,
    showCancelButton: true,
  })

  response.isConfirmed && Inertia.delete(route('superuser.menu.destroy', menu.id), {
    onSuccess: () => Inertia.get(route(route().current()))
  })
}

const submit = () => form.id ? update() : store()

const timeout = ref(null)
const save = () => {
  timeout.value && clearTimeout(timeout.value)
  timeout.value = setTimeout(() => {
    return useForm({ menus: menus.value }).patch(route('superuser.menu.save'), {
      onFinish: () => {
        clearTimeout(timeout.value)
        Inertia.get(route(route().current()))
      }
    }, 100)
  })
}

const esc = e => e.key === 'Escape' && close()

onMounted(fetch)
onMounted(() => window.addEventListener('keydown', esc))
onUnmounted(() => window.removeEventListener('keydown', esc))
</script>

<style src="@vueform/multiselect/themes/default.css"></style>
<style src="@/multiselect.css"></style>

<template>
  <DashboardLayout title="Menu">
    <Card class="bg-gray-50 dark:bg-gray-700 dark:text-gray-100">
      <template #header>
        <div class="flex items-center space-x-2 p-2 bg-gray-200 dark:bg-gray-800">
          <ButtonGreen v-if="can('create menu')" @click.prevent="show">
            <Icon name="plus" />
            <p class="uppercase font-semibold">create</p>
          </ButtonGreen>
        </div>
      </template>

      <template #body>
        <div class="flex flex-col space-y-1 p-2">
          <Nested :menus="menus" :edit="edit" :destroy="destroy" :save="save" />
        </div>
      </template>
    </Card>

    <Modal :show="open">
      <form @submit.prevent="submit" class="w-full max-w-xl sm:max-w-5xl h-fit rounded-md shadow-xl">
        <Card class="bg-gray-50 dark:bg-gray-700 dark:text-gray-100">
          <template #header>
            <div class="flex items-center space-x-2 p-2 justify-end bg-gray-200 dark:bg-gray-800">
              <Close @click.prevent="close" />
            </div>
          </template>

          <template #body>
            <div class="flex flex-col space-y-2 p-4">
              <div class="flex flex-col space-y-1">
                <div class="flex items-center space-x-2">
                  <label for="name" class="lowercase first-letter:capitalize w-1/3">name</label>
                  <Input v-model="form.name" type="text" name="name" placeholder="name" required autofocus />
                </div>

                <InputError :error="form.errors.name" />
              </div>

              <div class="flex flex-col space-y-1">
                <div class="flex items-center space-x-2">
                  <label for="route_or_url" class="lowercase first-letter:capitalize w-1/3">route name or url</label>
                  <Select
                    v-model="form.route_or_url"
                    :options="routes"
                    :searchable="true"
                    :createOption="true"
                    :value="form.route_or_url"
                    ref="route_or_url"
                    
                    placeholder="route name or url" />
                </div>

                <InputError :error="form.errors.route_or_url" />
              </div>

              <div class="flex flex-col space-y-1">
                <div class="flex items-center space-x-2">
                  <label for="actives" class="lowercase first-letter:capitalize w-1/3">actives</label>
                  <Select
                    v-model="form.actives"
                    :options="[...routes, ...form.actives.filter(active => ! routes.includes(active))]"
                    :searchable="true"
                    :closeOnSelect="false"
                    :clearOnSelect="false"
                    :createTag="true"
                    mode="tags"
                    ref="actives"
                    
                    placeholder="actives" />
                </div>

                <InputError :error="form.errors.actives" />
              </div>

              <div class="flex flex-col space-y-1">
                <div class="flex items-center space-x-2">
                  <label for="permissions" class="lowercase first-letter:capitalize w-1/3">permissions</label>
                  <Select
                    v-model="form.permissions"
                    :options="permissions.map(p => ({
                      label: p.name,
                      value: p.id,
                    }))"
                    :searchable="true"
                    :closeOnSelect="false"
                    :clearOnSelect="false"
                    mode="tags"
                    ref="permissions"
                    
                    placeholder="permissions" />
                </div>

                <InputError :error="form.errors.permissions" />
              </div>

              <div class="flex items-center space-x-2">
                <label class="flex-none lowercase first-letter:capitalize w-1/4">icon</label>

                <div class="flex items-center justify-between w-full">
                  <p class="text-3xl">
                    <Icon :name="form.icon" class="dark:text-white" />
                  </p>

                  <p class="text-sm uppercase">{{ form.icon }}</p>
                  
                  <ButtonBlue @click.prevent="icon = true" type="button">
                    <Icon name="edit" />
                    <p class="uppercase font-semibold">change</p>
                  </ButtonBlue>
                </div>
              </div>
            </div>
          </template>

          <template #footer>
            <div class="flex items-center justify-end space-x-2 bg-gray-200 dark:bg-gray-800 py-1 px-2">
              <ButtonGreen type="submit">
                <Icon name="check" />
                <p class="uppercase font-semibold">{{ form.id ? 'update' : 'create' }}</p>
              </ButtonGreen>
            </div>
          </template>
        </Card>
      </form>
    </Modal>

    <Modal :show="icon">
      <Card class="bg-gray-50 dark:bg-gray-700 dark:text-gray-100 w-full max-w-xl sm:max-w-5xl max-h-96 overflow-auto">
        <template #header>
          <div class="flex items-center space-x-2 p-2 justify-end bg-gray-200 dark:bg-gray-800 sticky top-0 left-0">
            <input type="search" v-model="search" class="py-1 w-full bg-white dark:bg-transparent rounded-md text-sm uppercase" placeholder="search something">
            <Icon @click.prevent="icon = false" name="times" class="px-2 py-1 bg-gray-300 hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-md transition-all cursor-pointer" />
          </div>
        </template>

        <template #body>
          <div class="flex-wrap p-4">
            <Icon v-for="(icx, i) in icons.filter(icx => icx.includes(search.trim().toLocaleLowerCase()))" :key="i" @click.prevent="form.icon = icx; icon = false" :name="icx" class="m-1 text-5xl px-2 py-1 text-gray-800 bg-gray-200 hover:bg-gray-100 dark:text-white dark:bg-gray-600 dark:hover:bg-gray-700 rounded-md cursor-pointer transition-all" />
          </div>
        </template>
      </Card>
    </Modal>
  </DashboardLayout>
</template>