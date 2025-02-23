<script setup>
import { getCurrentInstance, onMounted, onUnmounted, ref } from 'vue';
import { useForm, Link } from '@inertiajs/inertia-vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Card from '@/Components/Card.vue';
import Icon from '@/Components/Icon.vue';
import { Inertia } from '@inertiajs/inertia';
import Modal from '@/Components/Modal.vue'
import Button from '@/Components/Button.vue'
import Close from '@/Components/Button/Close.vue'
import ButtonDark from '@/Components/Button/Dark.vue'
import ButtonGreen from '@/Components/Button/Green.vue'
import ButtonBlue from '@/Components/Button/Blue.vue'
import ButtonRed from '@/Components/Button/Red.vue'

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
  <DashboardLayout :title="__('Document Approval')">
    <Card class="bg-white dark:bg-gray-700 dark:text-gray-200">
      <template #header>
        <div class="flex items-center space-x-2 bg-gray-200 dark:bg-gray-800 rounded-t-md p-2">
          <Link :href="route('document.index')">
            <ButtonDark class="bg-gray-700">
              <Icon name="caret-left" />
              <p class="uppercase font-semibold">{{ __('back') }}</p>
            </ButtonDark>
          </Link>

          <ButtonGreen v-if="(!document.pending && !document.approved) || document.rejected" @click.prevent="request">
            <Icon name="plus" />
            <p class="uppercase font-semibold">{{ __('request approval') }}</p>
          </ButtonGreen>
        </div>
      </template>

      <template #body>
        <div class="p-4">
          <div class="rounded-md overflow-auto border border-gray-300 dark:border-gray-900">
            <table class="w-full border-collapse">
              <thead class="bg-gray-200 dark:bg-gray-800 border-gray-300 dark:border-gray-900">
                <tr class="bg-inherit border-inherit">
                  <th class="border border-inherit py-2 text-center uppercase" rowspan="2">{{ __('no') }}</th>
                  <th v-for="(approver, i) in approvers" :key="i" class="border border-inherit text-center font-semibold uppercase px-3" colspan="3">
                    {{ approver.user.name }}
                  </th>
                </tr>

                <tr class="bg-inherit border-inherit">
                  <template v-for="(approver, i) in approvers" :key="i">
                    <th class="border border-inherit text-center font-semibold uppercase px-2">{{ __('status') }}</th>
                    <th class="border border-inherit text-center font-semibold uppercase px-2">{{ __('responded at') }}</th>
                    <th class="border border-inherit text-center font-semibold uppercase px-2">{{ __('note') }}</th>
                  </template>
                </tr>
              </thead>

              <tbody>
                <tr v-for="(approve, i) in approves" :key="i" class="hover:bg-gray-100 dark:hover:bg-gray-600 border-gray-300 dark:border-gray-800 transition-all">
                  <td class="border border-inherit text-center p-1">{{ i + 1 }}</td>
                  
                  <template v-for="(approval, j) in approve.approvals" :key="j">
                    <td class="border border-inherit text-center px-1">
                      <Button
                        :class="{
                          'bg-orange-600 hover:bg-orange-700': approval.status === 'pending',
                          'bg-red-600 hover:bg-red-700': approval.status === 'rejected',
                          'bg-green-600 hover:bg-green-700': approval.status === 'approved',
                        }">
                        <Icon :name="(approval.status === 'pending' && 'sync') || (approval.status === 'rejected' && 'times') || (approval.status === 'approved' && 'check')" />
                        <p class="uppercase font-semibold">{{ approval.status }}</p>
                      </Button>
                    </td>

                    <td class="border border-inherit text-center px-1">
                      {{ responded(approval) || '' }}
                    </td>

                    <td class="border border-inherit text-center px-1">
                      <ButtonBlue v-if="approval.responder_note" @click.prevent="note = approval.responder_note">
                        <Icon name="newspaper" />
                        <p class="uppercase font-semibold">{{ __('note') }}</p>
                      </ButtonBlue>
                    </td>
                  </template>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>
    </Card>
  </DashboardLayout>

  <Modal :show="note !== ''">
    <Card class="bg-white dark:bg-gray-700 dark:text-gray-200 w-full max-w-xl shadow-xl">
      <template #header>
        <div class="flex items-center justify-end space-x-2 bg-gray-200 dark:bg-gray-800 p-2">
          <Close @click.prevent="note = ''" />
        </div>
      </template>

      <template #body>
        <div class="p-4 max-h-96 overflow-auto">
          <p>{{ note }}</p>
        </div>
      </template>
    </Card>
  </Modal>
</template>