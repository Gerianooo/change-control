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
import Close from '@/Components/Button/Close.vue'
import Button from '@/Components/Button.vue'
import ButtonDark from '@/Components/Button/Dark.vue'
import ButtonGreen from '@/Components/Button/Green.vue'
import ButtonBlue from '@/Components/Button/Blue.vue'
import ButtonRed from '@/Components/Button/Red.vue'

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
          <Link :href="route('document.index')">
            <ButtonDark class="bg-gray-700">
              <Icon name="caret-left" />
              <p class="uppercase font-semibold">{{ __('back') }}</p>
            </ButtonDark>
          </Link>
          
          <ButtonGreen @click.prevent="store">
            <Icon name="plus" />
            <p class="uppercase font-semibold">{{ __('create') }}</p>
          </ButtonGreen>
        </div>
      </template>

      <template #body>
        <div class="flex flex-col space-y-4 p-4">
          <DataTable v-if="a" :url="route('api.v1.revision.paginate', document.id)">
            <template #thead="table">
              <tr class="bg-slate-100 dark:bg-gray-800">
                <Th :table="table" :sort="false" class="border border-slate-200 dark:border-gray-800 text-center whitespace-nowrap py-2">no</Th>
                <Th :table="table" :sort="true" name="code" class="border border-slate-200 dark:border-gray-800 text-center whitespace-nowrap py-2">{{ __('code') }}</Th>
                <Th :table="table" :sort="true" name="created_at" class="border border-slate-200 dark:border-gray-800 text-center whitespace-nowrap py-2">{{ __('created at') }}</Th>
                <Th :table="table" :sort="true" name="updated_at" class="border border-slate-200 dark:border-gray-800 text-center whitespace-nowrap py-2">{{ __('updated at') }}</Th>
                <Th :table="table" :sort="false" class="border border-slate-200 dark:border-gray-800 text-center whitespace-nowrap py-2">{{ __('expired at') }}</Th>
                <Th :table="table" :sort="false" class="border border-slate-200 dark:border-gray-800 text-center whitespace-nowrap py-2">{{ __('action') }}</Th>
              </tr>
            </template>

            <template #tfoot="table">
              <tr class="bg-slate-100 dark:bg-gray-800">
                <Th :table="table" :sort="false" class="border border-slate-200 dark:border-gray-800 text-center whitespace-nowrap py-2">no</Th>
                <Th :table="table" :sort="false" class="border border-slate-200 dark:border-gray-800 text-center whitespace-nowrap py-2">{{ __('code') }}</Th>
                <Th :table="table" :sort="false" class="border border-slate-200 dark:border-gray-800 text-center whitespace-nowrap py-2">{{ __('created at') }}</Th>
                <Th :table="table" :sort="false" class="border border-slate-200 dark:border-gray-800 text-center whitespace-nowrap py-2">{{ __('updated at') }}</Th>
                <Th :table="table" :sort="false" class="border border-slate-200 dark:border-gray-800 text-center whitespace-nowrap py-2">{{ __('expired at') }}</Th>
                <Th :table="table" :sort="false" class="border border-slate-200 dark:border-gray-800 text-center whitespace-nowrap py-2">{{ __('action') }}</Th>
              </tr>
            </template>

            <template #tbody="{ data, refresh }">
              <tr v-for="(revision, i) in data" :key="i">
                <td class="border border-slate-200 dark:border-gray-800 text-center py-1">{{ i + 1 }}</td>
                <td class="border border-slate-200 dark:border-gray-800 px-2 py-1">{{ revision.code }}</td>
                <td class="border border-slate-200 dark:border-gray-800 px-2 py-1">{{ new Date(revision.created_at).toLocaleString('id') }}</td>
                <td class="border border-slate-200 dark:border-gray-800 px-2 py-1">{{ new Date(revision.updated_at).toLocaleString('id') }}</td>
                <td class="border border-slate-200 dark:border-gray-800 px-2 py-1">{{ new Date(revision.expired_at).toLocaleString('id') }}</td>
                <td class="border border-slate-200 dark:border-gray-800 px-2 py-1">
                  <div class="flex items-center justify-center">
                    <div class="flex-wrap w-fit">
                      <Link v-if="revision.approve ? false : (revision.approved ? false : (revision.rejected ? false : !revision.pending))" :href="route('revision.approver', revision.id)">
                        <Button class="bg-orange-600 hover:bg-orange-600 m-[2px]">
                          <Icon name="user-cog" />
                          <p class="uppercase font-semibold">{{ __('approvers') }}</p>
                        </Button>
                      </Link>

                      <ButtonBlue @click.prevent="Inertia.get(route('revision.edit', revision.id))" class="m-[2px]">
                        <Icon name="edit" />
                        <p class="uppercase font-semibold">{{ __('edit') }}</p>
                      </ButtonBlue>

                      <ButtonRed @click.prevent="destroy(revision, refresh)" class="m-[2px]">
                        <Icon name="edit" />
                        <p class="uppercase font-semibold">{{ __('delete') }}</p>
                      </ButtonRed>
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
</template>