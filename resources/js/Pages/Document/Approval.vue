<script setup>
import { getCurrentInstance, onMounted, onUnmounted, ref } from 'vue';
import { useForm, Link } from '@inertiajs/inertia-vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Icon from '@/Components/Icon.vue';
import { Inertia } from '@inertiajs/inertia';

const self = getCurrentInstance()
const { document, approves, approvers } = defineProps({
  document: Object,
  approves: Array,
  approvers: Array,
})

const note = ref('')

const request = () => {
  return Inertia.post(route('document.approval.request', document.id))
}

const responded = approval => {
  if (['rejected', 'approved'].includes(approval.status)) {
    return approval.responded_at ? new Date(approval.responded_at).toLocaleString('id') : '-'
  }
}

const esc = e => e.key === 'Escape' && (note.value = '')

onMounted(() => window.addEventListener('keydown', esc))
onUnmounted(() => window.removeEventListener('keydown', esc))
</script>

<template>
  <DashboardLayout :title="__('approval')">
    <div class="flex flex-col bg-white rounded-md">
      <div class="flex items-center space-x-2 bg-slate-200 rounded-t-md p-2">
        <Link :href="route('document.index')" class="bg-slate-600 hover:bg-slate-700 rounded-md px-3 py-1 text-sm text-white transition-all">
          <div class="flex items-center space-x-1">
            <Icon name="caret-left" />
            <p class="uppercase font-semibold">{{ __('back') }}</p>
          </div>
        </Link>

        <button v-if="(!document.pending && !document.approved) || document.rejected" @click.prevent="request" class="bg-green-600 hover:bg-green-700 rounded-md px-3 py-1 text-sm text-white transition-all">
          <div class="flex items-center space-x-1">
            <Icon name="plus" />
            <p class="uppercase font-semibold">{{ __('request approval') }}</p>
          </div>
        </button>
      </div>

      <div class="flex flex-col space-y-4 p-4">
        <div class="rounded-md overflow-auto border border-slate-300">
          <table class="w-full border-collapse">
            <thead class="bg-slate-200">
              <tr>
                <th class="border border-slate-300 py-2 text-center uppercase" rowspan="2">{{ __('no') }}</th>
                <th v-for="(approver, i) in approvers" :key="i" class="border border-slate-300 text-center font-semibold uppercase px-2" colspan="3">
                  {{ approver.user.name }}
                </th>
              </tr>

              <tr>
                <template v-for="(approver, i) in approvers" :key="i">
                  <th class="border border-slate-300 text-center font-semibold uppercase px-2">{{ __('status') }}</th>
                  <th class="border border-slate-300 text-center font-semibold uppercase px-2">{{ __('responded at') }}</th>
                  <th class="border border-slate-300 text-center font-semibold uppercase px-2">{{ __('note') }}</th>
                </template>
              </tr>
            </thead>

            <tbody>
              <tr v-for="(approve, i) in approves" :key="i" class="hover:bg-slate-100 transition-all">
                <td class="border border-slate-300 text-center p-1">{{ i + 1 }}</td>
                
                <template v-for="(approval, j) in approve.approvals" :key="j">
                  <td class="border border-slate-300 text-center px-1">
                    <button
                      class="rounded-md px-3 py-1 text-sm text-white transition-all"
                      :class="(approval.status === 'pending' && 'bg-orange-600 hover:bg-orange-700') || (approval.status === 'rejected' && 'bg-red-600 hover:bg-red-700') || (approval.status === 'approved' && 'bg-green-600 hover:bg-green-700  ')">
                      <div class="flex items-center space-x-1">
                        <Icon :name="(approval.status === 'pending' && 'sync') || (approval.status === 'rejected' && 'times') || (approval.status === 'approved' && 'check')" />
                        <p class="uppercase font-semibold">{{ approval.status }}</p>
                      </div>
                    </button>
                  </td>

                  <td class="border border-slate-300 text-center px-1">
                    {{ responded(approval) || '' }}
                  </td>

                  <td class="border border-slate-300 text-center px-1">
                    <button v-if="approval.responder_note" @click.prevent="note = approval.responder_note" class="bg-blue-600 hover:bg-blue-700 rounded-md px-3 py-1 text-sm text-white transition-all">
                      <div class="flex items-center space-x-1">
                        <Icon name="newspaper" />
                        <p class="uppercase font-semibold">{{ __('note') }}</p>
                      </div>
                    </button>
                  </td>
                </template>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </DashboardLayout>

  <transition name="fade">
    <div v-if="note" class="fixed top-0 left-0 w-full h-screen bg-black bg-opacity-40 flex items-center justify-center overflow-auto">
      <div class="flex flex-col bg-white w-full max-w-xl rounded-md shadow-xl">
        <div class="flex items-center justify-end space-x-2 bg-slate-200 rounded-t-md p-2">
          <Icon @click.prevent="note = ''" name="times" class="bg-red-500 hover:bg-red-600 rounded-md px-2 py-1 text-sm text-white transition-all cursor-pointer" />
        </div>

        <div class="flex flex-col space-y-2 p-4 rounded-b-md">
          <p>{{ note }}</p>
        </div>
      </div>
    </div>
  </transition>
</template>