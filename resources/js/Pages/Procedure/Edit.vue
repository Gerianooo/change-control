<script setup>
import { getCurrentInstance, ref, onMounted, onUpdated, onUnmounted } from 'vue'
import { useForm, Link } from '@inertiajs/inertia-vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Card from '@/Components/Card.vue'
import Icon from '@/Components/Icon.vue'
import CKEditor from '@ckeditor/ckeditor5-vue'
import Swal from 'sweetalert2'
import ButtonDark from '@/Components/Button/Dark.vue'
import ButtonGreen from '@/Components/Button/Green.vue'

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

const ctrlAndSave = e => e.ctrlKey && e.key === 's' && (e.preventDefault() || submit())

onMounted(getFromStorage)

onMounted(() => window.addEventListener('keydown', ctrlAndSave))
onUnmounted(() => window.removeEventListener('keydown', ctrlAndSave))
</script>

<template>
  <DashboardLayout :title="__('Content')">
    <Card class="flex flex-col bg-white dark:bg-gray-700 dark:text-gray-200 rounded-md">
      <template #header>
        <div class="flex items-center space-x-1 rounded-t-md p-2 bg-slate-200 dark:bg-gray-800">
          <Link :href="route('revision.edit', procedure.revision_id)">
            <ButtonDark class="bg-gray-700">
              <Icon name="caret-left" />
              <p class="uppercase font-semibold">{{ __('back') }}</p>
            </ButtonDark>
          </Link>

          <ButtonGreen type="submit" form="form">
            <div class="flex items-center space-x-1">
              <Icon name="check" />
              <p class="uppercase font-semibold">{{ __('save') }}</p>
            </div>
          </ButtonGreen>
        </div>
      </template>

      <template #body>
        <form id="form" @submit.prevent="submit" class="flex flex-col">
          <div class="rounded text-gray-800">
            <CKEditorComponent v-model="form.value" :editor="editor" :config="config" @input="saveToStorage" />
          </div>
        </form>
      </template>
    </Card>
  </DashboardLayout>
</template>