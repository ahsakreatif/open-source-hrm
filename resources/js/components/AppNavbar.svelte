<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import { Button } from '@/components/ui/button';
    import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
    import { Menu, X } from 'lucide-svelte';
    import AppLogo from '@/components/AppLogo.svelte';
    import LanguageSwitcher from '@/components/LanguageSwitcher.svelte';
    import { t } from '@/stores/translation.js';

    let user = $derived($page.props.auth.user);

    let userRoles = $derived(user?.roles || []);
    let isEmployer = $derived(userRoles.some((role: any) => role.name === 'employer'));
    let isMenuOpen = $state(false);

    const navigationItems = [
        { name: t('frontend.navbar.home'), href: route('dashboard') },
        { name: t('frontend.navbar.opportunities'), href: route('opportunities.index') },
        // { name: t('frontend.navbar.about'), href: '#about' },
        // { name: t('frontend.navbar.contact'), href: '#contact' },
    ];
</script>

<nav class="bg-white/95 backdrop-blur-sm border-b border-gray-200 sticky top-0 z-50">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between h-16">
            <!-- Logo -->
            <div class="flex items-center">
                <Link href="/" class="flex items-center space-x-2">
                    <AppLogo />
                </Link>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-8">
                {#each navigationItems as item}
                    <Link
                        href={item.href}
                        class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium transition-colors duration-200"
                    >
                        {item.name}
                    </Link>
                {/each}
            </div>

            <!-- Desktop Auth Buttons -->
            <div class="hidden md:flex items-center space-x-4">
                <!-- Language Switcher -->
                <LanguageSwitcher />

                {#if user}
                    {#if !isEmployer}
                        <Link href={route('dashboard')}>
                            <Button variant="outline" size="sm">
                                {t('frontend.navbar.dashboard')}
                            </Button>
                        </Link>
                    {:else}
                    <Link href={route('filament.portal.pages.dashboard')} target="_blank">
                        <Button variant="outline" size="sm">
                            {t('frontend.navbar.portal')}
                        </Button>
                    </Link>
                    {/if}
                {:else}
                    <Link href={route('login')}>
                        <Button variant="ghost" size="sm">
                            {t('frontend.navbar.sign_in')}
                        </Button>
                    </Link>
                    <Link href={route('register')}>
                        <Button size="sm">
                            {t('frontend.navbar.get_started')}
                        </Button>
                    </Link>
                {/if}
            </div>

            <!-- Mobile menu button -->
            <div class="md:hidden">
                <Sheet bind:open={isMenuOpen}>
                    <SheetTrigger>
                        <Button variant="ghost" size="icon" class="h-9 w-9">
                            <Menu class="h-5 w-5" />
                        </Button>
                    </SheetTrigger>
                    <SheetContent side="right" class="w-[300px] p-6">
                        <SheetHeader class="flex justify-between items-center">
                            <SheetTitle class="text-left">
                                <div class="flex items-center space-x-2">
                                    <div class="w-6 h-6 bg-primary rounded-lg flex items-center justify-center">
                                        <span class="text-white font-bold text-xs">S</span>
                                    </div>
                                    <span class="text-lg font-bold text-gray-900">HRMS</span>
                                </div>
                            </SheetTitle>
                        </SheetHeader>

                        <div class="flex flex-col space-y-4 mt-8">
                            {#each navigationItems as item}
                                <Link
                                    href={item.href}
                                    class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium transition-colors duration-200"
                                >
                                    {item.name}
                                </Link>
                            {/each}

                            <!-- Language Switcher for Mobile -->
                            <div class="border-t border-gray-200 pt-4">
                                <div class="text-sm font-medium text-gray-700 mb-2">{t('frontend.navbar.language')}</div>
                                <LanguageSwitcher />
                            </div>

                            <div class="border-t border-gray-200 pt-4 mt-4">
                                {#if user}
                                    {#if !isEmployer}
                                        <Link href={route('dashboard')} class="block">
                                            <Button variant="outline" class="w-full">
                                                {t('frontend.navbar.dashboard')}
                                            </Button>
                                        </Link>
                                    {:else}
                                        <Link href={route('filament')} class="block">
                                            <Button variant="outline" class="w-full">
                                                {t('frontend.navbar.portal')}
                                            </Button>
                                        </Link>
                                    {/if}
                                {:else}
                                    <div class="space-y-3">
                                        <Link href={route('login')} class="block">
                                            <Button variant="ghost" class="w-full">
                                                {t('frontend.navbar.sign_in')}
                                            </Button>
                                        </Link>
                                        <Link href={route('register')} class="block">
                                            <Button class="w-full">
                                                {t('frontend.navbar.get_started')}
                                            </Button>
                                        </Link>
                                    </div>
                                {/if}
                            </div>
                        </div>
                    </SheetContent>
                </Sheet>
            </div>
        </div>
    </div>
</nav>
