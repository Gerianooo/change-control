<script setup>
import { getCurrentInstance, ref } from 'vue'
import { usePage, Link } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'
import Icon from '../Icon.vue'
import Swal from 'sweetalert2'

const open = ref(false)
const { user } = usePage().props.value

const logout = async () => {
  const response = await Swal.fire({
    title: 'Are you sure?',
    icon: 'question',
    showCloseButton: true,
    showCancelButton: true,
  })

  response.isConfirmed && Inertia.post(route('logout'))
}
</script>

<style scoped>
.slide-enter-active, .slide-leave-active {
  transition: all 500ms ease-in-out;
}

.slide-enter-from, .slide-leave-to {
  right: -100vw;
}

@media (min-width: 669px) {
  .slide-enter-from, .slide-leave-to {
    right: -15rem;
  }
}
</style>

<template>
  <div ref="container" class="flex-none flex items-center justify-end sm:justify-between space-x-2 sm:w-full sm:max-w-xs h-14 sm:px-3">
    <img :src="user.profile_photo_url" :alt="user.name" class="hidden sm:block flex-none rounded-full w-10 h-10">

    <p class="hidden sm:inline font-semibold lowercase first-letter:capitalize truncate w-full">{{ user.name }}</p>

    <div class="flex-none w-14 h-14 p-3">
      <button @click.prevent="open = ! open" class="rounded-md border border-gray-600 w-full h-full text-gray-700 transition-all ease-in-out duration-150 hover:border-gray-700 hover:text-gray-900">
        <Icon name="caret-left" class="transition-all ease-linear duration-500" :class="open && '-rotate-90'" />
      </button>
    </div>
  </div>

  <transition name="slide">
    <div v-if="open" class="fixed right-0 sm:right-4 top-12 w-full sm:max-w-xl sm:w-48 bg-white dark:bg-gray-700 rounded-md shadow-xl">
      <Link :href="route('profile.show')" as="button" class="w-full border-l-8 border-transparent dark:hover:border-gray-600 px-4 py-2 rounded-t-md transition-all ease-linear duration-150 hover:bg-gray-200 dark:hover:bg-gray-800">
        <div class="flex items-center space-x-2 dark:text-white font-semibold">
          <Icon name="user" />

          <p class="uppercase">profile</p>
        </div>
      </Link>

      <button @click.prevent="logout" class="w-full border-l-8 border-transparent dark:hover:border-gray-600 px-4 py-2 rounded-b-md transition-all ease-linear duration-150 hover:bg-gray-200 dark:hover:bg-gray-800">
        <div class="flex items-center space-x-2 dark:text-white font-semibold">
          <Icon name="door-open" />

          <p class="uppercase">logout</p>
        </div>
      </button>
    </div>
  </transition>
</template>