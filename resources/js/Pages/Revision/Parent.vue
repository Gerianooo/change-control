<script setup>
import { getCurrentInstance } from 'vue'
import Child from './Child.vue'

const self = getCurrentInstance()
const { procedure, childs, refresh } = defineProps({
  procedure: Object,
  childs: Array,
  refresh: Function,
  drag: Function,
  drop: Function,
  edit: Function,
})
</script>

<template>
  <div class="flex flex-col space-y-1">
    <Child :procedure="procedure" :refresh="refresh" :drag="drag" :drop="drop" :edit="edit" />

    <div class="flex flex-col space-y-1 ml-8">
      <slot />
    </div>
  </div>
</template>