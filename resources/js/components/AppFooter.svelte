<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import { Facebook, Twitter, Instagram, Linkedin, Mail, Phone, MapPin } from 'lucide-svelte';
    import { t } from '@/stores/translation.js';

    const currentYear = new Date().getFullYear();

    const footerLinks = {
        company: [
            { nameKey: 'frontend.footer.links.about_us', href: '#about' },
            { nameKey: 'frontend.footer.links.careers', href: '#careers' },
            { nameKey: 'frontend.footer.links.press', href: '#press' },
            { nameKey: 'frontend.footer.links.blog', href: '#blog' },
        ],
        support: [
            { nameKey: 'frontend.footer.links.help_center', href: '#help' },
            { nameKey: 'frontend.footer.links.contact_us', href: '#contact' },
            { nameKey: 'frontend.footer.links.privacy_policy', href: '#privacy' },
            { nameKey: 'frontend.footer.links.terms_of_service', href: '#terms' },
        ],
        resources: [
            { nameKey: 'frontend.footer.links.for_students', href: route('login') },
            { nameKey: 'frontend.footer.links.for_employers', href: route('register') },
            { nameKey: 'frontend.footer.links.browse_opportunities', href: route('opportunities.index') },
            { nameKey: 'frontend.footer.links.resources', href: '#resources' },
        ],
    };

    const socialLinks = [
        { name: 'Facebook', href: '#', icon: Facebook },
        { name: 'Twitter', href: '#', icon: Twitter },
        { name: 'Instagram', href: '#', icon: Instagram },
        { name: 'LinkedIn', href: '#', icon: Linkedin },
    ];
</script>

<footer class="bg-gray-900 text-white">
    <div class="container mx-auto px-4 py-12">
        <!-- Main Footer Content -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 mb-8">
            <!-- Brand Section -->
            <div class="lg:col-span-2">
                <div class="flex items-center space-x-2 mb-4">
                    <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-sm">S</span>
                    </div>
                    <span class="text-xl font-bold">HRMS</span>
                </div>
                <p class="text-gray-400 mb-6 max-w-md">
                    {t('frontend.footer.description')}
                </p>

                <!-- Contact Info -->
                <div class="space-y-3">
                    <div class="flex items-center space-x-3 text-gray-400">
                        <Mail class="h-4 w-4" />
                        <span>hello@HRMS.com</span>
                    </div>
                    <div class="flex items-center space-x-3 text-gray-400">
                        <Phone class="h-4 w-4" />
                        <span>+1 (555) 123-4567</span>
                    </div>
                    <div class="flex items-center space-x-3 text-gray-400">
                        <MapPin class="h-4 w-4" />
                        <span>Jakarta, Indonesia</span>
                    </div>
                </div>
            </div>

            <!-- Company Links -->
            <div>
                <h3 class="text-lg font-semibold mb-4">{t('frontend.footer.company')}</h3>
                <ul class="space-y-3">
                    {#each footerLinks.company as link}
                        <li>
                            <Link
                                href={link.href}
                                class="text-gray-400 hover:text-white transition-colors duration-200"
                            >
                                {t(link.nameKey)}
                            </Link>
                        </li>
                    {/each}
                </ul>
            </div>

            <!-- Support Links -->
            <div>
                <h3 class="text-lg font-semibold mb-4">{t('frontend.footer.support')}</h3>
                <ul class="space-y-3">
                    {#each footerLinks.support as link}
                        <li>
                            <Link
                                href={link.href}
                                class="text-gray-400 hover:text-white transition-colors duration-200"
                            >
                                {t(link.nameKey)}
                            </Link>
                        </li>
                    {/each}
                </ul>
            </div>

            <!-- Resources Links -->
            <div>
                <h3 class="text-lg font-semibold mb-4">{t('frontend.footer.resources')}</h3>
                <ul class="space-y-3">
                    {#each footerLinks.resources as link}
                        <li>
                            <Link
                                href={link.href}
                                class="text-gray-400 hover:text-white transition-colors duration-200"
                            >
                                {t(link.nameKey)}
                            </Link>
                        </li>
                    {/each}
                </ul>
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="border-t border-gray-800 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                <!-- Copyright -->
                <div class="text-gray-400 text-sm">
                    © {currentYear} HRMS. {t('frontend.footer.copyright')}
                </div>

                <!-- Social Links -->
                <div class="flex items-center space-x-4">
                    {#each socialLinks as social}
                        <Link
                            href={social.href}
                            class="text-gray-400 hover:text-white transition-colors duration-200"
                            aria-label={social.name}
                        >
                            <social.icon class="h-5 w-5" />
                        </Link>
                    {/each}
                </div>
            </div>
        </div>
    </div>
</footer>
