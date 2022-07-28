<script setup>
import { getCurrentInstance, nextTick, ref } from 'vue'
import { useForm, Link } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DataTable from '@/Components/DataTable/Builder.vue'
import Th from '@/Components/DataTable/Th.vue'
import Card from '@/Components/Card.vue'
import Icon from '@/Components/Icon.vue'
import Swal from 'sweetalert2'

const self = getCurrentInstance()
const { document } = defineProps({
  document: Object,
})
const open = ref(false)
const a = ref(true)
const form = useForm({
  id: null,
  document_id: document.id,
})

const show = () => {
  open.value = true
}

const close = () => {
  open.value = false
  form.clearErrors()
}

const store = () => {
  return form.post(route('revision.store'), {
    onSuccess: () => {
      close()
      form.reset()
      a.value = false
      nextTick(() => a.value = true)
    },
    onError: () => nextTick(show),
  })
}

const edit = (revision, refresh) => {
  form.id = revision.id
  form.name = revision.name
  form.code = revision.code
  form.max_revision_interval = revision.max_revision_interval
  
  show()

  Inertia.on('success', () => refresh())
}

const update = () => {
  return form.patch(route('revision.update', form.id), {
    onSuccess: () => close() || form.reset(),
    onError: () => nextTick(show),
  })
}

const destroy = async (revision, refresh) => {
  try {
    const response = await Swal.fire({
      title: __('are you sure') + '?',
      text: __('this will delete all of revision too and it can\'t recovered'),
      icon: 'warning',
      showCloseButton: true,
      showCancelButton: true,
    })

    if (response.isConfirmed) {
      Inertia.on('success', () => refresh())
      Inertia.delete(route('revision.destroy', revision.id))
    }
  } catch (e) {
    const response = await Swal.fire({
      title: __('are you want to try again') + '?',
      text: `${e}`,
      icon: 'error',
      showCancelButton: true,
      showCloseButton: true,
    })

    response.isConfirmed && destroy(revision, refresh)
  }
}

const submit = () => {
  return form.id ? update() : store()
}
</script>

<template>
  <DashboardLayout :title="__('Revision')">
    <Card class="flex flex-col space-y-2 bg-white dark:bg-gray-700 dark:text-gray-200 rounded-md">
      <template #header>
        <div class="flex items-center space-x-1 bg-slate-200 dark:bg-gray-800 p-2 rounded-t-md">
          <Link :href="route('document.index')" class="bg-slate-600 hover:bg-slate-700 rounded-md px-3 py-1 text-white text-sm transition-all">
            <div class="flex items-center space-x-1">
              <Icon name="caret-left" />
              <p class="uppercase font-semibold">{{ __('back') }}</p>
            </div>
          </Link>
          
          <button @click.prevent="store" class="bg-green-600 hover:bg-green-700 rounded-md px-3 py-1 text-sm text-white transition-all">
            <div class="flex items-center space-x-1">
              <Icon name="plus" />
              <p class="uppercase font-semibold">{{ __('create') }}</p>
            </div>
          </button>
        </div>
      </template>

      <template #body>
        <div class="flex flex-col space-y-4 p-4">
          <DataTable v-if="a" :url="route('api.v1.revision.paginate', document.id)" :sticky="true">
            <template v-slot:thead="table">
              <tr class="bg-slate-100 dark:bg-gray-800">
                <Th :table="table" :sort="false" class="border border-slate-200 dark:border-gray-800 text-center whitespace-nowrap py-2">no</Th>
                <Th :table="table" :sort="true" name="code" class="border border-slate-200 dark:border-gray-800 text-center whitespace-nowrap py-2">{{ __('code') }}</Th>
                <Th :table="table" :sort="true" name="created_at" class="border border-slate-200 dark:border-gray-800 text-center whitespace-nowrap py-2">{{ __('created at') }}</Th>
                <Th :table="table" :sort="true" name="updated_at" class="border border-slate-200 dark:border-gray-800 text-center whitespace-nowrap py-2">{{ __('updated at') }}</Th>
                <Th :table="table" :sort="false" class="border border-slate-200 dark:border-gray-800 text-center whitespace-nowrap py-2">{{ __('expired at') }}</Th>
                <Th :table="table" :sort="false" class="border border-slate-200 dark:border-gray-800 text-center whitespace-nowrap py-2">{{ __('action') }}</Th>
              </tr>
            </template>

            <template v-slot:tfoot="table">
              <tr class="bg-slate-100 dark:bg-gray-800">
                <Th :table="table" :sort="false" class="border border-slate-200 dark:border-gray-800 text-center whitespace-nowrap py-2">no</Th>
                <Th :table="table" :sort="false" class="border border-slate-200 dark:border-gray-800 text-center whitespace-nowrap py-2">{{ __('code') }}</Th>
                <Th :table="table" :sort="false" class="border border-slate-200 dark:border-gray-800 text-center whitespace-nowrap py-2">{{ __('created at') }}</Th>
                <Th :table="table" :sort="false" class="border border-slate-200 dark:border-gray-800 text-center whitespace-nowrap py-2">{{ __('updated at') }}</Th>
                <Th :table="table" :sort="false" class="border border-slate-200 dark:border-gray-800 text-center whitespace-nowrap py-2">{{ __('expired at') }}</Th>
                <Th :table="table" :sort="false" class="border border-slate-200 dark:border-gray-800 text-center whitespace-nowrap py-2">{{ __('action') }}</Th>
              </tr>
            </template>

            <template v-slot:tbody="{ data, refresh }">
              <tr v-for="(revision, i) in data" :key="i">
                <td class="border border-slate-200 dark:border-gray-800 text-center py-1">{{ i + 1 }}</td>
                <td class="border border-slate-200 dark:border-gray-800 px-2 py-1">{{ revision.code }}</td>
                <td class="border border-slate-200 dark:border-gray-800 px-2 py-1">{{ new Date(revision.created_at).toLocaleString('id') }}</td>
                <td class="border border-slate-200 dark:border-gray-800 px-2 py-1">{{ new Date(revision.updated_at).toLocaleString('id') }}</td>
                <td class="border border-slate-200 dark:border-gray-800 px-2 py-1">{{ new Date(revision.expired_at).toLocaleString('id') }}</td>
                <td class="border border-slate-200 dark:border-gray-800 px-2 py-1">
                  <div class="flex items-center justify-center">
                    <div class="flex-wrap w-fit">
                      <button @click.prevent="Inertia.get(route('revision.edit', revision.id))" class="bg-blue-600 hover:bg-blue-700 rounded-md px-3 py-1 text-sm transition-all m-[1px] text-white">
                        <div class="flex items-center space-x-1">
                          <Icon name="edit" />
                          <p class="uppercase font-semibold">{{ __('edit') }}</p>
                        </div>
                      </button>

                      <button @click.prevent="destroy(revision, refresh)" class="bg-red-600 hover:bg-red-700 rounded-md px-3 py-1 text-sm transition-all m-[1px] text-white">
                        <div class="flex items-center space-x-1">
                          <Icon name="edit" />
                          <p class="uppercase font-semibold">{{ __('delete') }}</p>
                        </div>
                      </button>
                    </div>
                  </div>
                </td>
              </tr>

              <tr v-if="data?.length === 0">
                <td colspan="1000" class="text-5xl font-semibold text-center p-4 lowercase first-letter:capitalize">
                  {{ __('there are no data available :\'(') }}
                </td>
              </tr>
            </template>
          </DataTable>
        </div>
      </template>
    </Card>
  </DashboardLayout>

  <transition name="fade">
    <div v-if="open" class="fixed top-0 left-0 w-full h-screen flex items-center justify-center bg-black bg-opacity-40">
      <form @submit.prevent="submit" class="w-full max-w-xl shadow-xl">
        <Card class="flex flex-col space-y-4 bg-white dark:bg-gray-700 dark:text-gray-200 rounded-md">
          <template #header>
            <div class="flex items-center space-x-2 justify-end p-2 bg-slate-200 dark:bg-gray-800 rounded-md">
              <Icon @click.prevent="close" name="times" class="px-2 py-1 rounded-md bg-slate-100 hover:bg-slate-50 transition-all cursor-pointer" />
            </div>
          </template>

          <template #body>
            <div class="flex flex-col space-y-4 p-4">
              <div class="flex flex-col space-y-2">
                <div class="flex items-center space-x-2">
                  <label for="name" class="lowercase first-letter:capitalize w-1/3">{{ __('name') }}</label>
                  <input type="text" name="name" v-model="form.name" class="w-full bg-transparent rounded-md border border-slate-200 dark:border-gray-800 px-3 py-1 uppercase" required :placeholder="__('name')">
                </div>

                <div v-if="form.errors.name" class="text-sm text-red-500 text-right">{{ form.errors.name }}</div>
              </div>

              <div class="flex flex-col space-y-2">
                <div class="flex items-center space-x-2">
                  <label for="code" class="lowercase first-letter:capitalize w-1/3">{{ __('code') }}</label>
                  <input type="text" name="code" v-model="form.code" class="w-full bg-transparent rounded-md border border-slate-200 dark:border-gray-800 px-3 py-1 uppercase" required :placeholder="__('code')">
                </div>

                <div v-if="form.errors.code" class="text-sm text-red-500 text-right">{{ form.errors.code }}</div>
              </div>

              <div class="flex flex-col space-y-2">
                <div class="flex items-center space-x-2">
                  <label for="max_revision_interval" class="lowercase first-letter:capitalize w-1/3">{{ __('revision interval') }}</label>
                  <input type="number" name="max_revision_interval" min="1" v-model="form.max_revision_interval" class="w-full bg-transparent rounded-md border border-slate-200 dark:border-gray-800 px-3 py-1 uppercase" required :placeholder="__('revision interval')">
                </div>

                <div v-if="form.errors.max_revision_interval" class="text-sm text-red-500 text-right">{{ form.errors.max_revision_interval }}</div>
              </div>
            </div>
          </template>

          <template #footer>
            <div class="flex items-center justify-end space-x-2 px-2 py-1 bg-slate-200 dark:bg-gray-800 rounded-b-md">
              <button type="submit" class="bg-slate-700 hover:bg-slate-800 rounded-md px-3 py-1 text-sm text-white transition-all">
                <div class="flex items-center space-x-1">
                  <Icon name="check" />
                  <p class="uppercase font-semibold">{{ __(form.id ? 'update' : 'create') }}</p>
                </div>
              </button>
            </div>
          </template>
        </Card>
      </form>
    </div>
  </transition>
</template>