<script setup>
import { getCurrentInstance, ref } from 'vue';

const self = getCurrentInstance()
const { procedure } = defineProps({
  procedure: Object,
})

const content = ref(procedure.content)

Echo.private(`App.Models.Content.${procedure.content?.id}`)
    .notification(notification => {
      content.value.value = notification.value

      Toast.fire({
        title: 'Update detected',
        text: 'Content will changed',
        icon: 'info',
        timer: 2000,
        showCloseButton: true,
      })
    })
</script>

<template>
  <div class="flex flex-col space-y-2">
    <div class="flex items-center space-x-2 font-semibold text-md text-md">
      <h1>{{ procedure.position }}.</h1>
      <h1>{{ procedure.name }}</h1>
    </div>

    <div class="w-full" v-html="content?.value || ''"></div>
  </div>
</template>