<script setup>
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
});
</script>

<template>
<h2 class="font-semibold text-xl text-gray-100 leading-tight">
    Editar Usuário
</h2>
<div>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <div v-if="$page.props.jetstream.canUpdateProfileInformation">
            <UpdateProfileInformationForm :user="$page.props.auth.user" />
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
            <DeleteUserForm class="mt-10 sm:mt-0" />
    </div>
</div>
</template>
