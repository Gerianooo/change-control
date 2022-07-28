<script setup>
import { getCurrentInstance, ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Builder from '@/Components/DataTable/Builder.vue'
import Th from '@/Components/DataTable/Th.vue'
import Icon from '@/Components/Icon.vue'
import { Inertia } from '@inertiajs/inertia';
import Swal from 'sweetalert2';
import { useForm } from '@inertiajs/inertia-vue3';

const self = getCurrentInstance()
const table = ref({
  refresh: null,
})

const fork = (data, refresh) => {
  table.value.refresh = refresh
  return data
}

const fetch = () => table.value.refresh && table.value.refresh()

const preview = document => {
  console.log(document)
}

const approve = async document => {
  const response = await Swal.fire({
    title: __('are you sure') + '?',
    icon: 'question',
    showCancelButton: true,
    showCloseButton: true,
  })

  response.isConfirmed && Inertia.patch(route('document.approve', document.id))
}

const reject = async document => {
  const response = await Swal.fire({
    title: __('are you sure') + '?',
    text: __('if you reject this, all of current pending approval will rejected too'),
    icon: 'warning',
    showCloseButton: true,
    showCancelButton: true,
    input: 'textarea',
    inputPlaceholder: 'rejected note',
    inputValidator: value => {
      if (!value) {
        return __('you must write your reason. why you reject this document?!')
      }
    },
  })

  response.isConfirmed && useForm({ note: response.value }).patch(route('document.reject', document.id))
}

Inertia.on('finish', e => fetch())
</script>

<template>
  <DashboardLayout title="Approval">
    <div class="flex flex-col space-x-2 bg-white rounded-md">
      <div class="flex items-center space-x-2 bg-slate-200 rounded-t-md p-2">
        <p class="text-md lowercase first-letter:capitalize font-semibold">{{ __('approval for me') }}</p>
      </div>

      <div class="flex flex-col space-y-2 p-4">
        <Builder :url="route('approval.document.paginate')">
          <template v-slot:thead="table">
            <tr class="bg-slate-200">
              <Th class="border border-slate-300 text-center uppercase p-2" :table="table" :sort="false">{{ __('no') }}</Th>
              <Th class="border border-slate-300 text-center uppercase p-2 min-w-[24rem] whitespace-nowrap" :table="table" name="name">{{ __('name') }}</Th>
              <Th class="border border-slate-300 text-center uppercase p-2" :table="table" name="max_revision_interval">{{ __('revision interval') }}</Th>
              <Th class="border border-slate-300 text-center uppercase p-2" :table="table" :sort="false">{{ __('action') }}</Th>
            </tr>
          </template>

          <template v-slot:tbody="{ data, refresh }">
            <template v-if="data?.filter(d => ! d.approved)?.length > 0">
              <tr v-for="(document, i) in fork(data.filter(d => ! d.approved), refresh)" :key="i">
                <td class="border border-slate-200 text-center py-1">{{ i + 1 }}</td>
                <td class="border border-slate-200 px-2 py-1">{{ __(document.name) }}</td>
                <td class="border border-slate-200 px-2 py-1">{{ __(document.max_revision_interval) }}</td>
                <td class="border border-slate-200 px-2 py-1">
                  <div class="flex-wrap text-center">
                    <button @click.prevent="preview(document)" class="bg-blue-600 hover:bg-blue-700 rounded-md px-3 py-1 text-sm text-white transition-all m-[1px]">
                      <div class="flex items-center space-x-1">
                        <Icon name="play" />
                        <p class="uppercase font-semibold">{{ __('preview') }}</p>
                      </div>
                    </button>

                    <button @click.prevent="approve(document)" class="bg-green-600 hover:bg-green-700 rounded-md px-3 py-1 text-sm text-white transition-all m-[1px]">
                      <div class="flex items-center space-x-1">
                        <Icon name="check" />
                        <p class="uppercase font-semibold">{{ __('approve') }}</p>
                      </div>
                    </button>

                    <button @click.prevent="reject(document)" class="bg-red-600 hover:bg-red-700 rounded-md px-3 py-1 text-sm text-white transition-all m-[1px]">
                      <div class="flex items-center space-x-1">
                        <Icon name="times" />
                        <p class="uppercase font-semibold">{{ __('reject') }}</p>
                      </div>
                    </button>
                  </div>
                </td>
              </tr>
            </template>

            <tr v-else>
              <td class="p-4 text-center text-3xl lowercase first-letter:capitalize" colspan="1000">
                {{ __('you are not have any pending approval :\'(') }}
              </td>
            </tr>
          </template>
        </Builder>
      </div>
    </div>
  </DashboardLayout>
</template>