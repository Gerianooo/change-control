<script setup>
import { getCurrentInstance, nextTick, onMounted, onUnmounted, ref } from 'vue'
import { useForm, Link } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Builder from '@/Components/DataTable/Builder.vue'
import Th from '@/Components/DataTable/Th.vue'
import Card from '@/Components/Card.vue'
import Icon from '@/Components/Icon.vue'
import Modal from '@/Components/Modal.vue'
import Close from '@/Components/Button/Close.vue'
import ButtonGreen from '@/Components/Button/Green.vue'
import ButtonBlue from '@/Components/Button/Blue.vue'
import ButtonRed from '@/Components/Button/Red.vue'
import Button from '@/Components/Button.vue'
import Swal from 'sweetalert2'
import Input from '@/Components/Input.vue'
import InputError from '@/Components/InputError.vue'

const self = getCurrentInstance()
const open = ref(false)
const table = ref(null)
const form = useForm({
  id: null,
  name: '',
  code: '',
  max_revision_interval: 36,
})

const show = () => {
  open.value = true
  nextTick(() => self.refs.name?.focus())
}

const close = () => {
  open.value = false
  form.clearErrors()
}

const store = () => {
  return form.post(route('document.store'), {
    onSuccess: () => {
      close()
      form.reset()
    },
    onError: () => nextTick(show),
    onFinish: () => table.value?.refresh(),
  })
}

const edit = document => {
  form.id = document.id
  form.name = document.name
  form.code = document.code
  form.max_revision_interval = document.max_revision_interval
  
  show()
}

const update = () => {
  return form.patch(route('document.update', form.id), {
    onSuccess: () => close() || form.reset(),
    onError: () => nextTick(show),
    onFinish: () => table.value?.refresh(),
  })
}

const destroy = async document => {
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
      Inertia.delete(route('document.destroy', document.id))
    }
  } catch (e) {
    const response = await Swal.fire({
      title: __('are you want to try again') + '?',
      text: `${e}`,
      icon: 'error',
      showCancelButton: true,
      showCloseButton: true,
    })

    response.isConfirmed && destroy(document)
  }
}

const submit = () => form.id ? update() : store()

const esc = e => e.key === 'Escape' && close()

onMounted(() => window.addEventListener('keydown', esc))
onUnmounted(() => window.removeEventListener('keydown', esc))
</script>

<template>
  <DashboardLayout :title="__('Document')">
    <Card class="bg-white dark:bg-gray-700 dark:text-gray-200">
      <template #header>
        <div class="flex items-center space-x-2 bg-slate-200 dark:bg-gray-800 p-2">
          <ButtonGreen @click.prevent="show">
            <Icon name="plus" />
            <p class="uppercase font-semibold">{{ __('create') }}</p>
          </ButtonGreen>
        </div>
      </template>

      <template #body>
        <div class="p-4">
          <Builder ref="table" :url="route('api.v1.document.paginate')" :sticky="true">
            <template #thead="table">
              <tr class="bg-slate-100 dark:bg-gray-800 border-slate-200 dark:border-gray-900">
                <Th :table="table" :sort="false" class="border text-center whitespace-nowrap px-1 py-2">no</Th>
                <Th :table="table" :sort="true" name="name" class="border text-center whitespace-nowrap px-3 py-2">{{ __('name') }}</Th>
                <Th :table="table" :sort="true" name="code" class="border text-center whitespace-nowrap px-3 py-2">{{ __('code') }}</Th>
                <Th :table="table" :sort="true" name="max_revision_interval" class="border text-center whitespace-nowrap px-3 py-2">{{ __('revision interval month') }}</Th>
                <Th :table="table" :sort="true" name="created_at" class="border text-center whitespace-nowrap px-3 py-2">{{ __('created at') }}</Th>
                <Th :table="table" :sort="true" name="updated_at" class="border text-center whitespace-nowrap px-3 py-2">{{ __('updated at') }}</Th>
                <Th :table="table" :sort="false" class="border text-center whitespace-nowrap px-3 py-2">{{ __('last revision') }}</Th>
                <Th :table="table" :sort="false" class="border text-center whitespace-nowrap px-3 py-2">{{ __('action') }}</Th>
              </tr>
            </template>

            <template #tfoot="table">
              <tr class="bg-slate-100 dark:bg-gray-800 border-slate-200 dark:border-gray-900">
                <Th :table="table" :sort="false" class="border text-center py-2">no</Th>
                <Th :table="table" :sort="false" class="border text-center whitespace-nowrap py-2">{{ __('name') }}</Th>
                <Th :table="table" :sort="false" class="border text-center whitespace-nowrap py-2">{{ __('code') }}</Th>
                <Th :table="table" :sort="false" class="border text-center whitespace-nowrap py-2">{{ __('revision interval month') }}</Th>
                <Th :table="table" :sort="false" class="border text-center whitespace-nowrap py-2">{{ __('created at') }}</Th>
                <Th :table="table" :sort="false" class="border text-center whitespace-nowrap py-2">{{ __('updated at') }}</Th>
                <Th :table="table" :sort="false" class="border text-center whitespace-nowrap py-2">{{ __('last revision') }}</Th>
                <Th :table="table" :sort="false" class="border text-center whitespace-nowrap py-2">{{ __('action') }}</Th>
              </tr>
            </template>

            <template #tbody="{ data, empty, processing }">
              <TransitionGroup
                enterActiveClass="transition-all duration-200"
                leaveActiveClass="transition-all duration-200"
                enterFromClass="opacity-0 -scale-y-100"
                leaveToClass="opacity-0 -scale-y-100">
                <template v-if="empty">
                  <tr>
                    <td colspan="1000" class="text-5xl font-semibold text-center p-4 lowercase first-letter:capitalize">
                      {{ __('there are no data available') }}
                    </td>
                  </tr>
                </template>

                <template v-else>
                  <tr
                    v-for="(document, i) in data"
                    :key="i"
                    class="border-slate-200 dark:border-gray-800 dark:hover:bg-slate-600 transition-all duration-300"
                    :class="processing && 'dark:bg-gray-600'">
                    <td class="border border-inherit text-center py-1">{{ i + 1 }}</td>
                    <td class="border border-inherit px-2 py-1">{{ document.name }}</td>
                    <td class="border border-inherit px-2 py-1">{{ document.code }}</td>
                    <td class="border border-inherit px-2 py-1">{{ document.max_revision_interval }} Month</td>
                    <td class="border border-inherit px-2 py-1">{{ new Date(document.created_at).toLocaleString('id') }}</td>
                    <td class="border border-inherit px-2 py-1">{{ new Date(document.updated_at).toLocaleString('id') }}</td>
                    <td class="border border-inherit px-2 py-1">{{ document.revision?.code }}</td>
                    <td class="border border-inherit px-2 py-1">
                      <div class="flex items-center justify-center">
                        <div class="flex-wrap w-fit">
                          <Button v-if="document.approved" @click.prevent="Inertia.get(route('document.revisions', document.id))" class="bg-emerald-600 hover:bg-emerald-700 m-[1px]">
                            <Icon name="list" />
                            <p class="uppercase font-semibold">{{ __('revisions') }}</p>
                          </Button>

                          <Button v-if="document.approve ? false : (document.approved ? false : (document.rejected ? false : !document.pending))" @click.prevent="Inertia.get(route('document.approvers', document.id))" class="bg-orange-600 hover:bg-orange-700 m-[1px]">
                            <Icon name="user-cog" />
                            <p class="uppercase font-semibold">{{ __('approvers') }}</p>
                          </Button>

                          <Button v-if="document.approvers_count > 0" @click.prevent="Inertia.get(route('document.approvals', document.id))" class="bg-cyan-600 hover:bg-cyan-700 m-[1px]">
                            <Icon name="user-check" />
                            <p class="uppercase font-semibold">{{ __('approvals') }}</p>
                          </Button>

                          <ButtonBlue v-if="document.approved ? false : (document.rejected ? true : !document.pending)" @click.prevent="edit(document)" class="m-[1px]">
                            <Icon name="edit" />
                            <p class="uppercase font-semibold">{{ __('edit') }}</p>
                          </ButtonBlue>

                          <ButtonRed v-if="document.approved ? false : (document.rejected ? true : !document.pending)" @click.prevent="destroy(document)" class="m-[1px]">
                            <Icon name="edit" />
                            <p class="uppercase font-semibold">{{ __('delete') }}</p>
                          </ButtonRed>
                        </div>
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
          <div class="flex items-center space-x-2 justify-end p-2 bg-slate-200 dark:bg-gray-800">
            <Close @click.prevent="close" />
          </div>
        </template>

        <template #body>
          <div class="flex flex-col space-y-4 p-4">
            <div class="flex flex-col space-y-2">
              <div class="flex items-center space-x-2">
                <label for="name" class="lowercase first-letter:capitalize w-1/3">{{ __('name') }}</label>
                <Input
                  v-model="form.name"
                  type="text"
                  name="name"
                  :placeholder="__('name')"
                  required
                  autofocus />
              </div>

              <InputError :error="form.errors.name" />
            </div>

            <div class="flex flex-col space-y-2">
              <div class="flex items-center space-x-2">
                <label for="code" class="lowercase first-letter:capitalize w-1/3">{{ __('code') }}</label>
                <Input
                  v-model="form.code"
                  type="text"
                  name="code"
                  :placeholder="__('code')"
                  required />
              </div>

              <InputError :error="form.errors.code" />
            </div>

            <div class="flex flex-col space-y-2">
              <div class="flex items-center space-x-2">
                <label for="max_revision_interval" class="lowercase first-letter:capitalize w-1/3">{{ __('revision interval') }}</label>
                <Input
                  v-model="form.max_revision_interval"
                  type="number"
                  name="max_revision_interval"
                  min="1"
                  :placeholder="__('revision interval')"
                  required />
              </div>

              <InputError :error="form.errors.max_revision_interval" />
            </div>
          </div>
        </template>

        <template #footer>
          <div class="flex items-center justify-end space-x-2 px-2 py-1 bg-slate-200 dark:bg-gray-800">
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