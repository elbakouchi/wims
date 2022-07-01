<template>
  <div class="md:flex md:shrink-0">
    <div
      v-if="$page.props.settings.sidebar == 'mini'"
      class="bg-gray-900 md:shrink-0 md:w-16 px-6 py-3 flex items-center justify-between md:justify-center"
    >
      <inertia-link class="text-gray-100" :href="route('dashboard')">
        <tec-application-mark class="block h-8" />
      </inertia-link>
      <mobile-menu-icon @on-change="v => (show = v)" />
    </div>
    <div v-else class="bg-gray-900 md:shrink-0 md:w-64 px-6 py-3 flex items-center justify-between md:justify-center">
      <inertia-link class="text-gray-100" :href="route('dashboard')">
        <tec-application-logo class="block w-56" />
      </inertia-link>
      <mobile-menu-icon @on-change="v => (show = v)" />
    </div>
    <div class="bg-white border-b w-full py-2 px-4 md:py-0 md:text-md flex justify-between items-center">
      <!-- <div class="mt-1 mr-4">{{ $page.props.user.name.account.name }}</div> -->
      <div class="mt-1 mr-4 flex items-center">
        <tec-dropdown align="left" width="48">
          <template #trigger>
            <button
              class="flex items-center px-2 py-1 mr-2 border-2 border-transparent rounded-md focus:outline-none focus:border-gray-300 transition duration-150 ease-in-out"
            >
              <span class="" v-html="lang.replace(/./g, char => String.fromCodePoint(char.charCodeAt(0) + 127397))"> </span>
            </button>
          </template>

          <template #content>
            <template v-for="lang in $page.props.languages" :key="lang.value">
              <tec-dropdown-link as="button" @click="changeLang(lang.value)">
                <span class="mr-2" v-html="lang.flag.replace(/./g, char => String.fromCodePoint(char.charCodeAt(0) + 127397))"> </span>
                {{ $t(lang.label) }}
              </tec-dropdown-link>
            </template>
          </template>
        </tec-dropdown>

        <tec-dropdown align="left" width="56" v-if="$can(['create-contacts', 'create-users', 'create-roles'])">
          <template #trigger>
            <button
              class="flex items-center p-1 border-2 border-transparent rounded-md focus:outline-none focus:border-gray-300 transition duration-150 ease-in-out"
            >
              <icons name="plus" class="-ml-1 mr-1"></icons>
              {{ $t('New') }}
            </button>
          </template>

          <template #content>
            <tec-dropdown-link v-if="$can('create-checkins')" :href="route('checkins.create')">
              {{ $t('create_x', { x: $t('Checkin') }) }}
            </tec-dropdown-link>
            <tec-dropdown-link v-if="$can('create-checkouts')" :href="route('checkouts.create')">
              {{ $t('create_x', { x: $t('Checkout') }) }}
            </tec-dropdown-link>
            <tec-dropdown-link v-if="$can('create-items')" :href="route('items.create')">
              {{ $t('create_x', { x: $t('Item') }) }}
            </tec-dropdown-link>
            <tec-dropdown-link v-if="$can('create-contacts')" :href="route('contacts.create')">
              {{ $t('create_x', { x: $t('Contact') }) }}
            </tec-dropdown-link>
            <tec-dropdown-link v-if="$can('create-categories')" :href="route('categories.create')">
              {{ $t('create_x', { x: $t('Category') }) }}
            </tec-dropdown-link>
            <tec-dropdown-link v-if="$can('create-units')" :href="route('units.create')">
              {{ $t('create_x', { x: $t('Unit') }) }}
            </tec-dropdown-link>
            <tec-dropdown-link v-if="$can('create-warehouses')" :href="route('warehouses.create')">
              {{ $t('create_x', { x: $t('Warehouse') }) }}
            </tec-dropdown-link>
            <tec-dropdown-link v-if="$can('create-users')" :href="route('users.create')">
              {{ $t('create_x', { x: $t('User') }) }}
            </tec-dropdown-link>
            <tec-dropdown-link v-if="$can('create-roles')" :href="route('roles.create')">
              {{ $t('create_x', { x: $t('Role') }) }}
            </tec-dropdown-link>
          </template>
        </tec-dropdown>
      </div>

      <div class="flex items-center gap-x-3">
        <icon-menus class="flex gap-x-3 items-center" />

        <tec-dropdown align="right" width="48">
          <template #trigger>
            <button
              class="flex items-center p-1 rounded-full focus:outline-none focus:ring-2 focus:ring-gray-300 transition duration-150 ease-in-out"
            >
              <img
                :alt="$page.props.user.name"
                :src="$page.props.user.profile_photo_url"
                class="h-8 w-8 rounded-full object-cover sm:mr-2"
                v-if="$page.props.jetstream.managesProfilePhotos"
              />
              <span class="hidden sm:inline-flex items-center mr-2">
                {{ $page.props.user.name }}
                <icons name="chevron-down" class="ml-2 -mr-1" />
              </span>
            </button>
          </template>

          <template #content>
            <div class="block px-4 py-2 text-xs text-gray-400">{{ $t('Manage Account') }}</div>
            <tec-dropdown-link :href="route('profile.show')"> {{ $t('Profile') }} </tec-dropdown-link>
            <tec-dropdown-link :href="route('api-tokens.index')" v-if="$page.props.jetstream.hasApiFeatures">
              {{ $t('API Tokens') }}
            </tec-dropdown-link>

            <div class="border-t border-gray-100"></div>
            <form @submit.prevent="logout">
              <tec-dropdown-link as="button"> {{ $t('Log Out') }} </tec-dropdown-link>
            </form>
          </template>
        </tec-dropdown>
      </div>
    </div>
  </div>
</template>

<script>
import IconMenus from './IconMenus.vue';
import TecDropdown from '@/Jetstream/Dropdown.vue';
import TecDropdownLink from '@/Jetstream/DropdownLink.vue';
import MobileMenuIcon from '@/Shared/Top/MobileMenuIcon.vue';
import TecApplicationLogo from '@/Jetstream/ApplicationLogo.vue';
import TecApplicationMark from '@/Jetstream/ApplicationMark.vue';

export default {
  emits: ['on-mobile-menu-change'],
  components: {
    IconMenus,
    TecDropdown,
    MobileMenuIcon,
    MobileMenuIcon,
    TecDropdownLink,
    TecApplicationLogo,
    TecApplicationMark,
  },

  data() {
    return { show: false };
  },

  watch: {
    show(show) {
      this.$emit('on-mobile-menu-change', show);
    },
  },

  computed: {
    lang() {
      if (window.Locale == 'es') {
        return 'ES';
      } else if (window.Locale == 'pt') {
        return 'PT';
      }
      return 'US';
    },
  },

  methods: {
    logout() {
      this.$inertia.post(route('logout'));
    },

    changeLang(lang) {
      this.$inertia.visit('/language/' + lang, {
        onFinish: () => {
          setTimeout(() => {
            window.location.reload();
          }, 1000);
        },
      });
    },
  },
};
</script>
