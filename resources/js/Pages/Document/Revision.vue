<script setup>
import { getCurrentInstance, nextTick, onMounted, onUnmounted, ref } from 'vue'
import { useForm, Link } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Builder from '@/Components/DataTable/Builder.vue'
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
import Modal from '@/Components/Modal.vue'
import Input from '@/Components/Input.vue'
import InputError from '@/Components/InputError.vue'
import Action from '@/Components/Button/Action.vue'
import axios from 'axios'

const self = getCurrentInstance()
const { document } = defineProps({
  document: Object,
})
const table = ref(null)
const open = ref(false)
const form = useForm({
  id: null,
  document_id: document.id,
  classification: 'minor',
  reason_change: '',
  attachments: [],
})

const show = () => {
  open.value = true

  nextTick(() => self.refs.reason_change?.focus())
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
    },
    onError: () => nextTick(show),
    onFinish: () => table.value?.refresh(),
  })
}

const edit = revision => {
  form.id = revision.id
  form.document_id = revision.document_id
  form.classification = revision.classification
  form.reason_change = revision.reason_change
  form.attachments = revision.attachments.map(attachment => ({
    id: attachment.id,
    name: attachment.name,
    file: attachment.url,
    filename: attachment.filename,
  }))

  show()
}

const update = () => {
  return form.patch(route('revision.update', form.id), {
    onSuccess: () => {
      close()
      
      nextTick(() => table.value?.refresh())
    },
  })
}

const destroy = async revision => {
  try {
    const response = await Swal.fire({
      title: __('are you sure') + '?',
      text: __('this will delete all of revision too and it can\'t recovered'),
      icon: 'warning',
      showCloseButton: true,
      showCancelButton: true,
    })

    if (response.isConfirmed) {
      Inertia.on('finish', () => table.value?.refresh())
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

    response.isConfirmed && destroy(revision)
  }
}

const submit = () => {
  return form.id ? update() : store()
}

const detach = async attachment => {
  let response = await Swal.fire({
    title: __('are you sure') + '?',
    text: __('after deleted you can\'t recover it'),
    icon: 'warning',
    showCloseButton: true,
    showCancelButton: true,
  })
  
  if (response.isConfirmed) {
    useForm({}).delete(route('attachment.destroy', attachment.id), {
      onFinish: () => form.attachments = form.attachments.filter(a => a.id !== attachment.id),
      onError: async () => {
        const response = await Swal.fire({
          title: __('error, are you want to try again') + '?',
          text: `${e}`,
          icon: 'error',
          showCancelButton: true,
          showCloseButton: true,
        })

        response.isConfirmed && detach(attachment)
      },
    })
  }
}

const esc = e => e.key === 'Escape' && close()

onMounted(() => window.addEventListener('keydown', esc))
onUnmounted(() => window.removeEventListener('keydown', esc))
</script>

<template>
  <DashboardLayout :title="__('Revision')">
    <Card class="bg-white dark:bg-gray-700 dark:text-gray-200">
      <template #header>
        <div class="flex items-center space-x-1 bg-slate-200 dark:bg-gray-800 p-2">
          <Link :href="route('document.index')">
            <ButtonDark class="bg-gray-700">
              <Icon name="caret-left" />
              <p class="uppercase font-semibold">{{ __('back') }}</p>
            </ButtonDark>
          </Link>
          
          <ButtonGreen @click.prevent="(form.id = null) || show()">
            <Icon name="plus" />
            <p class="uppercase font-semibold">{{ __('create') }}</p>
          </ButtonGreen>
        </div>
      </template>

      <template #body>
        <div class="p-4">
          <Builder ref="table" :url="route('api.v1.revision.paginate', document.id)">
            <template #thead="table">
              <tr class="bg-slate-100 dark:bg-gray-800 border-slate-200 dark:border-gray-900">
                <Th :table="table" :sort="false" class="border text-center whitespace-nowrap py-2">{{ __('no') }}</Th>
                <Th :table="table" :sort="true" name="code" class="border text-center whitespace-nowrap py-2">{{ __('code') }}</Th>
                <Th :table="table" :sort="true" name="classification" class="border text-center whitespace-nowrap py-2">{{ __('classification') }}</Th>
                <Th :table="table" :sort="true" name="reason_change" class="border text-center py-2">{{ __('reason change') }}</Th>
                <Th :table="table" :sort="true" name="created_at" class="border text-center whitespace-nowrap py-2">{{ __('created at') }}</Th>
                <Th :table="table" :sort="false" class="border text-center whitespace-nowrap py-2">{{ __('action') }}</Th>
              </tr>
            </template>

            <template #tfoot="table">
              <tr class="bg-slate-100 dark:bg-gray-800 border-slate-200 dark:border-gray-900">
                <Th :table="table" :sort="false" class="border text-center whitespace-nowrap py-2">{{ __('no') }}</Th>
                <Th :table="table" :sort="false" class="border text-center whitespace-nowrap py-2">{{ __('code') }}</Th>
                <Th :table="table" :sort="false" class="border text-center whitespace-nowrap py-2">{{ __('classification') }}</Th>
                <Th :table="table" :sort="false" class="border text-center py-2">{{ __('reason change') }}</Th>
                <Th :table="table" :sort="false" class="border text-center whitespace-nowrap py-2">{{ __('created at') }}</Th>
                <Th :table="table" :sort="false" class="border text-center whitespace-nowrap py-2">{{ __('action') }}</Th>
              </tr>
            </template>

            <template #tbody="{ data, empty, processing }">
              <TransitionGroup
                enterActiveClass="transition-all duration-300"
                leaveActiveClass="transition-all duration-300"
                enterFromClass="opacity-0 -scale-y-100"
                leaveToClass="opacity-0 -scale-y-100">
                <template v-if="empty">
                  <tr>
                    <td colspan="1000" class="text-5xl font-semibold text-center p-4 lowercase first-letter:capitalize">
                      {{ __('there are no data available :\'(') }}
                    </td>
                  </tr>
                </template>
                  
                <template v-else>
                  <tr v-for="(revision, i) in data" :key="i" class="dark:hover:bg-gray-600 border-slate-200 dark:border-gray-800 transition-all duration-300" :class="processing && 'dark:bg-gray-600'">
                    <td class="border border-inherit text-center py-1">{{ i + 1 }}</td>
                    <td class="border border-inherit px-2 py-1">{{ revision.code }}</td>
                    <td class="border border-inherit px-2 py-1 text-center">
                      <Button :class="{
                        'bg-orange-500 hover:bg-orange-600': revision.classification === 'major',
                        'bg-blue-600 hover:bg-blue-700': revision.classification === 'minor',
                      }">
                        <p class="uppercase font-bold">{{ revision.classification }}</p>
                      </Button>
                    </td>
                    <td class="border border-inherit px-2 py-1">{{ revision.reason_change }}</td>
                    <td class="border border-inherit px-2 py-1">{{ new Date(revision.created_at).toLocaleString('id') }}</td>
                    <td class="border border-inherit px-2 py-1">
                      <div class="flex items-center justify-center">
                        <Action>
                          <Link v-if="revision.approve ? false : (revision.approved ? false : (revision.rejected ? false : !revision.pending))" :href="route('revision.approver', revision.id)" class="w-full">
                            <Button class="bg-orange-500 hover:bg-orange-600 active:bg-orange-700 w-full m-[1px]">
                              <Icon name="user-cog" />
                              <p class="uppercase font-semibold">{{ __('approvers') }}</p>
                            </Button>
                          </Link>

                          <ButtonBlue @click.prevent="edit(revision)" class="w-full">
                            <Icon name="edit" />
                            <p class="uppercase font-semibold">edit</p>
                          </ButtonBlue>

                          <Button v-if="revision.approvers_count > 0" @click.prevent="Inertia.get(route('revision.approvals', revision.id))" class="bg-cyan-600 hover:bg-cyan-700 w-full m-[1px]">
                            <Icon name="user-check" />
                            <p class="uppercase font-semibold">{{ __('approvals') }}</p>
                          </Button>

                          <Link :href="route('revision.edit', revision.id)">
                            <Button class="bg-fuchsia-600 hover:bg-fuchsia-700 active:bg-fuchsia-800 w-full m-[1px]">
                              <Icon name="newspaper" />
                              <p class="uppercase font-semibold">{{ __('contents') }}</p>
                            </Button>
                          </Link>

                          <ButtonRed v-if="revision.approved ? false : (revision.rejected ? true : !revision.pending)" @click.prevent="destroy(revision)" class="w-full m-[1px]">
                            <Icon name="edit" />
                            <p class="uppercase font-semibold">{{ __('delete') }}</p>
                          </ButtonRed>
                        </Action>
                      </div>
                    </td>
                  </tr>
                </template>
              </TransitionGroup>
            </template>
          </Builder>
        </div>
      </template>
    </Card>
  </DashboardLayout>

  <Modal :show="open">
    <form @submit.prevent="submit" class="w-full max-w-xl h-fit shadow-xl">
      <Card class="bg-white dark:bg-gray-700 dark:text-gray-200">
        <template #header>
          <div class="flex items-center space-x-2 justify-end bg-gray-200 dark:bg-gray-800 p-2">
            <Close @click.prevent="close" />
          </div>
        </template>

        <template #body>
          <div class="flex flex-col space-y-2 p-4 max-h-96 overflow-auto">
            <div class="flex flex-col space-y-1">
              <div class="flex flex-col space-y-2">
                <label for="reason_change" class="lowercase first-letter:capitalize font-semibold">{{ __('reason change') }}</label>

                <textarea
                  v-model="form.reason_change"
                  name="reason_change"
                  class="w-full bg-white dark:bg-transparent border-gray-300 dark:border-gray-600 dark:text-white dark:placeholder:text-white focus:border-indigo-300 dark:focus:border-gray-700 focus:ring focus:ring-indigo-200 dark:focus:ring-gray-600 focus:ring-opacity-50 rounded-md shadow-sm placeholder:capitalize"
                  ref="reason_change"
                  :placeholder="__('reason change') + '...'"
                  required></textarea>
              </div>

              <InputError :error="form.errors.reason_change" />
            </div>

            <div class="flex flex-col space-y-1">
              <div class="flex items-center space-x-2">
                <div class="flex items-center space-x-1">
                  <input type="radio" name="major" v-model="form.classification" value="major" :checked="form.classification === 'major'" class="dark:bg-gray-600">
                  <label @click.prevent="form.classification = 'major'" for="major" class="font-bold uppercase cursor-pointer">{{ __('major') }}</label>
                </div>

                <div class="flex items-center space-x-1">
                  <input type="radio" name="minor" v-model="form.classification" value="minor" :checked="form.classification === 'minor'" class="dark:bg-gray-600">
                  <label @click.prevent="form.classification = 'minor'" for="minor" class="font-bold uppercase cursor-pointer">{{ __('minor') }}</label>
                </div>
              </div>

              <InputError :error="form.errors.classification" />
            </div>

            <Transition
              enterActiveClass="transition-all duration-300"
              leaveActiveClass="transition-all duration-300"
              enterFromClass="opacity-0"
              leaveToClass="opacity-0">
              <div v-if="form.attachments.length" class="flex flex-col space-y-2">
                <TransitionGroup
                  enterActiveClass="transition-all duration-300"
                  leaveActiveClass="transition-all duration-300"
                  enterFromClass="opacity-0"
                  leaveToClass="opacity-0">
                  <div v-for="(attachment, i) in form.attachments" :key="i" class="flex-none flex items-center space-x-2">
                    <Input
                      v-model="attachment.name"
                      :name="`attachment:name:${i}`"
                      type="text"
                      class="w-1/4"
                      :placeholder="__('name')"
                      required />

                    <div v-if="form.id && attachment.id" class="w-full flex items-center justify-center space-x-1">
                      <Icon name="download" />
                      <a :href="attachment.file" :download="`${attachment.name}-${attachment.filename}`" class="uppercase font-semibold text-sm">
                        download
                      </a>
                    </div>

                    <Input
                      v-else
                      :name="`attachment:file:${i}`"
                      type="file"
                      @change.prevent="attachment.file = $event.target.files[0]"
                      required />

                    <Icon
                      v-if="form.id && attachment.id"
                      @click.prevent="detach(attachment)"
                      name="times"
                      class="px-2 py-1 rounded-md bg-red-500 hover:bg-red-600 active:bg-red-700 text-white transition-all duration-300 cursor-pointer" />
                    
                    <Icon
                      v-else
                      @click.prevent="form.attachments = form.attachments.filter((a, j) => i !== j)"
                      name="times"
                      class="px-2 py-1 rounded-md bg-red-500 hover:bg-red-600 active:bg-red-700 text-white transition-all duration-300 cursor-pointer" />
                  </div>
                </TransitionGroup>
              </div>
            </Transition>
          </div>
        </template>

        <template #footer>
          <div class="flex items-center space-x-2 justify-end bg-gray-200 dark:bg-gray-800 px-2 py-1">
            <ButtonBlue
              v-if="!form.id"
              @click.prevent="form.attachments.push({
                name: '',
                file: null,
              })"
              type="button">
              <Icon name="plus" />
              <p class="uppercase font-semibold">{{ __('attachment') }}</p>
            </ButtonBlue>

            <ButtonGreen type="submit">
              <Icon name="check" />
              <p class="uppercase font-semibold">{{ form.id ? 'update' : 'create' }}</p>
            </ButtonGreen>
          </div>
        </template>
      </Card>
    </form>
  </Modal>
</template>