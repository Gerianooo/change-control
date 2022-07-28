<script setup>
import { getCurrentInstance, ref, onMounted, onUpdated } from 'vue'
import { useForm, Link } from '@inertiajs/inertia-vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Card from '@/Components/Card.vue'
import Icon from '@/Components/Icon.vue'
import CKEditor from '@ckeditor/ckeditor5-vue'
import Swal from 'sweetalert2'

const editor = CKSource.Editor
const self = getCurrentInstance()
const { procedure, content } = defineProps({
  procedure: Object,
  content: Object,
})
const key = `procedure:${procedure.id}`

const form = useForm({
  procedure_id: procedure.id,
  value: content?.value,
})

const config = CKSource.Editor.builtinPlugins.map(p => p.pluginName)
const CKEditorComponent = CKEditor.component

const submit = () => {
  return form.post(route('content.store'), {
    onSuccess: () => deleteFromStorage(),
  })
}

const getFromStorage = () => localStorage.getItem(key) && (form.value = localStorage.getItem(key))
const saveToStorage = () => localStorage.setItem(key, form.value)
const deleteFromStorage = () => localStorage.removeItem(key)

onMounted(getFromStorage)
</script>

<template>
  <DashboardLayout :title="__('content')">
    <Card class="flex flex-col bg-white dark:bg-gray-700 dark:text-gray-200 rounded-md">
      <template #header>
        <div class="flex items-center space-x-1 rounded-t-md p-2 bg-slate-200 dark:bg-gray-800">
          <Link :href="route('revision.edit', procedure.revision_id)" class="bg-slate-600 hover:bg-slate-700 rounded-md px-3 py-1 text-white text-sm transition-all">
            <div class="flex items-center space-x-1">
              <Icon name="caret-left" />
              <p class="uppercase font-semibold">{{ __('back') }}</p>
            </div>
          </Link>

          <button type="submit" form="form" class="bg-green-600 hover:bg-green-700 rounded-md px-3 py-1 text-white text-sm transition-all">
            <div class="flex items-center space-x-1">
              <Icon name="check" />
              <p class="uppercase font-semibold">{{ __('save') }}</p>
            </div>
          </button>
        </div>
      </template>

      <template #body>
        <form id="form" @submit.prevent="submit" class="flex flex-col">
          <div class="rounded">
            <CKEditorComponent v-model="form.value" :editor="editor" :config="config" @input="saveToStorage" />
          </div>
        </form>
      </template>
    </Card>
  </DashboardLayout>
</template>