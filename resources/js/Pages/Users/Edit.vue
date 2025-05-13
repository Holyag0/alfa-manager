<script setup>
import DeleteUserForm from '@/Pages/Users/components/DeleteUserForm.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Users/components/LogoutOtherBrowserSessionsForm.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import TwoFactorAuthenticationForm from '@/Pages/Users/components/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from '@/Pages/Users/components/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Users/components/UpdateProfileInformationForm.vue';

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
    user:Object
});
</script>

<template>
<h2 class="font-semibold text-xl text-gray-100 leading-tight">
    Editar Usuário
</h2>
<div>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <div v-if="$page.props.jetstream.canUpdateProfileInformation">
            <UpdateProfileInformationForm :user="user" />
            <SectionBorder />
        </div>

        <div v-if="$page.props.jetstream.canUpdatePassword">
            <UpdatePasswordForm class="mt-10 sm:mt-0" />
            <SectionBorder />
        </div>

        <div v-if="$page.props.jetstream.canManageTwoFactorAuthentication">
            <TwoFactorAuthenticationForm :requires-confirmation="confirmsTwoFactorAuthentication" class="mt-10 sm:mt-0" />
            <SectionBorder />
        </div>
            <LogoutOtherBrowserSessionsForm :sessions="sessions" class="mt-10 sm:mt-0" />
            <SectionBorder />
            <DeleteUserForm class="mt-10 sm:mt-0" :user="user"  />
    </div>
</div>
</template>
