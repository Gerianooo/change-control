<script setup>
import { getCurrentInstance, onMounted, onUpdated, ref } from 'vue'
import Dragable from 'vuedraggable'
import Icon from '@/Components/Icon.vue'
import Swal from 'sweetalert2'
import { Inertia } from '@inertiajs/inertia'

const self = getCurrentInstance()
const { procedures, edit, save } = defineProps({
  procedures: Array,
  save: Function,
  edit: Function,
})

const click = element => {
  Inertia.get(route('procedure.edit', element.id))
}

const destroy = async procedure => {
  const response = await Swal.fire({
    title: 'Are you sure want to delete?',
    text: 'It will delete all child too and can\'t be restored',
    icon: 'question',
    showCloseButton: true,
    showCancelButton: true,
  })

  response.isConfirmed && Inertia.delete(route('procedure.destroy', procedure.id))
}
</script>

<template>
  <Dragable
    tag="ul"
    :list="procedures"
    :group="{ name: 'g1' }"
    item-key="id"
    @change="save">
    <template #item="{ element }">
      <div class="flex flex-col space-y-1">
        <div class="flex items-center space-x-2 bg-gray-200 hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-600 rounded-md px-4 py-2 transition-all duration-300 cursor-move">
          <div @click.prevent="!element.childs?.length && click(element)" class="flex items-center space-x-2 w-full" :class="{
            'cursor-pointer': !element.childs?.length,
            'cursor-move': !!element.childs?.length,
          }">
            <p class="font-bold">{{ element.position }}.</p>
            <p class="uppercase">{{ element.name }}</p>
          </div>

          <div ref="container" class="flex-none flex items-center rounded-md">
            <Icon @click.prevent="edit(element)" name="edit" class="bg-blue-600 hover:bg-blue-700 px-2 py-1 rounded-l-md text-sm text-white transition-all cursor-pointer" />
            <Icon @click.prevent="destroy(element)" name="trash" class="bg-red-600 hover:bg-red-700 px-2 py-1 rounded-r-md text-sm text-white transition-all cursor-pointer" />
          </div>
        </div>

        <Nested :procedures="element.childs" :edit="edit" :save="save" class="ml-10" />
      </div>
    </template>
  </Dragable>
</template>