<script lang="ts">
    import InputError from '@/components/InputError.svelte';
    import { Link } from '@inertiajs/svelte';
    import GoogleOAuthButton from '@/components/GoogleOAuthButton.svelte';
    import { Button } from '@/components/ui/button';
    import { Checkbox } from '@/components/ui/checkbox';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
    import AuthBase from '@/layouts/AuthLayout.svelte';
    import { useForm } from '@inertiajs/svelte';
    import { LoaderCircle } from 'lucide-svelte';
    import { t } from '@/stores/translation.js';

    interface Props {
        status?: string;
        canResetPassword: boolean;
        isGoogleAuthEnabled: boolean;
    }

    let { status, canResetPassword, isGoogleAuthEnabled }: Props = $props();

    const form = useForm({
        email: '',
        password: '',
        remember: false,
    });


    const submit = (e: Event) => {
        e.preventDefault();

        console.log('Form submitted with:', {
            email: $form.email,
            password: $form.password ? '***' : 'empty',
            remember: $form.remember
        });

        // This app is only for employees, so always submit to employee login
        $form.post(route('employee.login'), {
            onFinish: () => {
                console.log('Form submission finished');
                $form.reset('password');
            },
            onError: (errors) => {
                console.error('Form submission errors:', errors);
            },
            onSuccess: (page) => {
                console.log('Form submission successful:', page);
            }
        });
    };
</script>

<svelte:head>
    <title>Login</title>
</svelte:head>

<div class="flex h-screen">
    <!-- Left column - Hero image -->
    <div class="hidden lg:flex w-1/2 bg-gray-100 items-center justify-center">
        <div class="max-w-md p-8">
            <img
                src="/images/header.png"
                alt="Login Header"
                class="w-full h-auto"
            />
            <h2 class="mt-8 text-2xl font-bold text-center text-gray-900">{t('frontend.auth.login.welcome_back')}</h2>
            <p class="mt-4 text-center text-gray-600">{t('frontend.auth.login.welcome_description')}</p>
        </div>
    </div>

    <!-- Right column - Login form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center">
        <div class="w-full max-w-md px-6">
            <AuthBase title={t('frontend.auth.login.title')} description={t('frontend.auth.login.description')}>
                {#if status}
                    <div class="mb-4 text-center text-sm font-medium text-green-600">
                        {status}
                    </div>
                {/if}

                <!-- Google OAuth Button (for students only) -->
                {#if isGoogleAuthEnabled}
                    <div class="mb-6">
                        <GoogleOAuthButton />
                    </div>

                    <div class="relative mb-6">
                        <div class="absolute inset-0 flex items-center">
                            <span class="w-full border-t"></span>
                        </div>
                        <div class="relative flex justify-center text-xs uppercase">
                            <span class="bg-background px-2 text-muted-foreground">{t('frontend.auth.login.or_continue_with')}</span>
                        </div>
                    </div>
                {/if}

                <form onsubmit={submit} class="flex flex-col gap-6">
                    <div class="grid gap-6">
                        <div class="grid gap-2">
                            <Label for="email">{t('frontend.auth.login.email_address')}</Label>
                            <Input
                                id="email"
                                type="email"
                                required
                                autofocus
                                tabindex={1}
                                autocomplete="email"
                                bind:value={$form.email}
                                placeholder="email@example.com"
                            />
                            <InputError message={$form.errors.email} />
                        </div>

                        <div class="grid gap-2">
                            <div class="flex items-center justify-between">
                                <Label for="password">{t('frontend.auth.login.password')}</Label>
                                {#if canResetPassword}
                                    <Link href={route('password.request')} class="text-sm" tabindex={5}>{t('frontend.auth.login.forgot_password')}</Link>
                                {/if}
                            </div>
                            <Input
                                id="password"
                                type="password"
                                required
                                tabindex={2}
                                autocomplete="current-password"
                                bind:value={$form.password}
                                placeholder="Password"
                            />
                            <InputError message={$form.errors.password} />
                        </div>

                        <div class="flex items-center justify-between">
                            <Label for="remember" class="flex items-center space-x-3">
                                <Checkbox id="remember" bind:checked={$form.remember} tabindex={3} />
                                <span>{t('frontend.auth.login.remember_me')}</span>
                            </Label>
                        </div>

                        <Button type="submit" class="mt-4 w-full" tabindex={4} disabled={$form.processing}>
                            {#if $form.processing}
                                <LoaderCircle class="h-4 w-4 animate-spin" />
                            {/if}
                            {t('frontend.auth.login.log_in')}
                        </Button>
                    </div>

                    <div class="text-center text-sm text-muted-foreground">
                        {t('frontend.auth.login.dont_have_account')}
                        <Link href={route('register')} data={ {role: 'employee' } } tabindex={5}>{t('frontend.auth.login.sign_up')}</Link>
                    </div>
                </form>
            </AuthBase>
        </div>
    </div>
</div>

