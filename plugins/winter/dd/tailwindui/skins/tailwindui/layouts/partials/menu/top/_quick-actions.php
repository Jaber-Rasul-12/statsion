<!--
    Quick Create button
    @TODO:
        - Refactor QuickAction items to be able to render themselves via a
        partial with the additional aim of supporting dropdown menus defined
        within said partial with the usage of certain classes. This would then
        be used for the QuickCreate button as well as the UserProfile menu
        - Unhide when implemented
-->
<div class="shrink-0 hidden">
    <headless-menu
        as="div"
        class="ml-3 relative">
        <headless-menu-button type="button" class="btn btn-primary relative inline-flex items-center px-4 py-2 shadow-sm">
            <span>Create</span>
            <plus-sm-icon class="ml-2 h-4 w-4" aria-hidden="true" />
        </headless-menu-button>

        <transition enter-active-class="transition ease-out duration-100" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <headless-menu-items class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black dark:ring-gray-500 ring/5 focus:outline-none">
                <div class="py-1">
                    <headless-menu-item v-slot="{ active }">
                        <a href="#" :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm']">Something</a>
                    </headless-menu-item>
                    <headless-menu-item v-slot="{ active }">
                        <a href="#" :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm']">Something else</a>
                    </headless-menu-item>
                    <headless-menu-item v-slot="{ active }">
                        <a href="#" :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm']">Some other thing</a>
                    </headless-menu-item>
                </div>
            </headless-menu-items>
        </transition>
    </headless-menu>
</div>

<div class="flex items-center ml-4 shrink-0">
    <!--
        Notifications item
        @TODO:
            - Implement as QuickAction item provided by future Winter.Notifications
            plugin or perhaps in the core backend / system module
    -->

    <?php



    if ((config('winter.tailwindui::show_notifications', false))) {
        $preferences = StudioBosco\BackendNotifications\Helpers\BackendNotifications::getPreferences();
        if ($preferences->enable_notifications) {
    ?>
            <headless-menu
                as="div"
                class="ml-3 relative">
                <div>
                    <headless-menu-button
                        class=" flex text-sm rounded-full focus:outline-none">
                        <button
                            type="button"
                            class="quick-link<?= $menuLocation === 'side' ? ' quick-link-light' : '' ?> ">
                            <span class="absolute -top-1 -right-1 p-0.5 bg-red-500 rounded-full text-xxs text-white font-semibold leading-none z-10">
                                <?php
                                echo StudioBosco\BackendNotifications\Helpers\BackendNotifications::getCount(); ?>
                            </span>
                            <span class="sr-only"><?= e(trans('winter.tailwindui::lang.plugin.View_notifications')) ?></span>
                            <bell-icon class="h-6 w-6" aria-hidden="true" />
                        </button>
                    </headless-menu-button>
                </div>
                <transition
                    enter-active-class="transition ease-out duration-100"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-75"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95">
                    <headless-menu-items
                        class="origin-top-right absolute right-0 mt-2 py-1 w-64 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black dark:ring-gray-500 ring/5 divide-y divide-gray-200 dark:divide-gray-500 focus:outline-none z-50">
                        <?php

                        $notifications = \StudioBosco\BackendNotifications\Models\Notification::listBackend()
                            ->orderBy('created_at', 'desc')
                            ->take(5)
                            ->get();
                        ?>

                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white"> <?= e(trans('winter.tailwindui::lang.plugin.Notifications')) ?> <bell-icon class="h-6 w-6" style="display: inline-block; color: blue;" aria-hidden="true" /> </h3>
                        </div>

                        <ul class="max-h-72 overflow-y-auto">
                            <?php if (!empty($notifications)): ?>
                                <?php foreach ($notifications as $note): ?>
                                    <li class="flex px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-700 transition notifications-list__item is-<?php echo $note->type; ?>">
                                        <a href="<?= Backend::url('studioBosco/backendnotifications/notifications/preview/' . $note->id) ?>"
                                            class="flex items-start w-full">
                                            <div class="flex-shrink-0 pt-1">
                                                <button
                                                    type="button"
                                                    style="padding: 10px;"
                                                    class="quick-link<?= $menuLocation === 'side' ? ' quick-link-light' : '' ?> ">
                                                    <span class="absolute -top-1 -right-1 p-0.5 bg-blue-500 rounded-full text-xxs text-white font-semibold leading-none z-10">
                                                        <bell-icon class="h-6 w-6" aria-hidden="true" />
                                                </button>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <p class="text-sm text-gray-700 dark:text-gray-100 font-medium">
                                                    <?= e($note->subject) ?>
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    <?= \Str::limit($note->body, 20) ?>
                                                </p>
                                                <p class="text-xs text-gray-400 mt-1">
                                                    <?= $note->created_at ?>
                                                </p>
                                            </div>
                                        </a>
                                    </li>

                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    <?= e(trans('winter.tailwindui::lang.plugin.No_notifications')) ?>
                                </li>
                            <?php endif; ?>
                        </ul>

                        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-center">

                            <a href="<?= Backend::url('studioBosco/backendnotifications/notifications') ?>" class="btn btn-default"><?= e(trans('winter.tailwindui::lang.plugin.View_All')) ?></a>
                        </div>


                    </headless-menu-items>
                </transition>
            </headless-menu>

    <?php }
    } ?>

    <!-- End Notificiton  -->


    <!-- quick link actions -->
    <?php foreach (BackendMenu::listQuickActionItems() as $item): ?>
        <a
            href="<?= $item->url ?>"
            title="<?= e(trans($item->label)) ?>"
            <?= Html::attributes($item->attributes) ?>
            class="quick-link<?= $menuLocation === 'side' ? ' quick-link-light' : '' ?>">

            <?php if ($item->iconSvg): ?>
                <img
                    src="<?= Url::asset($item->iconSvg) ?>"
                    class="svg-icon h-6 w-6" loading="lazy" />
            <?php endif ?>

            <i class="<?= $item->iconSvg ? 'svg-replace' : null ?> <?= $item->icon ?> text-2xl"></i>
        </a>
    <?php endforeach ?>



    <!-- user profile menu -->
    <headless-menu
        as="div"
        class="ml-3 relative">
        <div>
            <headless-menu-button
                class="bg-gray-800 flex text-sm rounded-full focus:outline-none">
                <!-- @TODO: Needs translation -->
                <span class="sr-only">Open user menu</span>
                <img
                    class="h-8 w-8 rounded-full"
                    src="<?= $this->user->getAvatarThumb(90, ['mode' => 'crop', 'extension' => 'png']) ?>"
                    loading="lazy"
                    alt="<?= e(trans('backend::lang.account.signed_in_as', ['full_name' => $this->user->full_name])) ?>" />
            </headless-menu-button>
        </div>

        <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95">
            <headless-menu-items
                class="origin-top-right absolute right-0 mt-2 py-1 w-64 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black dark:ring-gray-500 ring/5 divide-y divide-gray-200 dark:divide-gray-500 focus:outline-none z-50">
                <div class="px-4 py-3">
                    <div class="shrink-0 group block">
                        <div class="flex items-center">
                            <div>
                                <img class="inline-block h-8 w-8 rounded-full" src="<?= $this->user->getAvatarThumb(90, ['mode' => 'crop', 'extension' => 'png']) ?>" alt="<?= $this->user->full_name ?>" />
                            </div>
                            <div class="ml-3">
                                <p class="text-sm">
                                    <?= e(trans('backend::lang.account.signed_in_as', ['full_name' => null])) ?>
                                </p>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-500 truncate">
                                    <?= $this->user->full_name ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php foreach ($mySettings as $category => $items): ?>
                    <div class="py-1">
                        <?php foreach ($items as $item): ?>
                            <headless-menu-item>
                                <a
                                    href="<?= $item->url ?>"
                                    class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 hover:no-underline dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-700">
                                    <i class="<?= $item->icon ?> mr-2 text-sm text-center min-w-[1.25em] text-gray-400 group-hover:text-gray-500 dark:group-hover:text-white"></i>
                                    <?= e(trans($item->label)) ?>
                                </a>
                            </headless-menu-item>
                        <?php endforeach ?>
                    </div>
                <?php endforeach ?>
                <?php if (BackendAuth::user() && BackendAuth::user()->hasAccess('winter.tailwindui.manage_own_appearance.dark_mode')) : ?>
                    <div class="py-1">
                        <?= Form::open(['class' => 'px-4 py-1']) ?>
                        <div class="grid grid-cols-3 gap-2">
                            <headless-menu-item>
                                <button
                                    type="button"
                                    data-request="onTailwindUISetTheme"
                                    data-request-data="dark_mode: 'auto'"
                                    data-request-success="updateColorScheme(data.dark_mode);"
                                    class="btn-darkmode"
                                    title="<?= e(trans('winter.tailwindui::lang.preferences.dark_mode.auto')) ?>">
                                    <i class="icon-computer"></i>
                                </button>
                            </headless-menu-item>
                            <headless-menu-item>
                                <button
                                    type="button"
                                    data-request="onTailwindUISetTheme"
                                    data-request-data="dark_mode: 'light'"
                                    data-request-success="updateColorScheme(data.dark_mode);"
                                    class="btn-darkmode"
                                    title="<?= e(trans('winter.tailwindui::lang.preferences.dark_mode.light')) ?>">
                                    <i class="icon-sun"></i>
                                </button>
                            </headless-menu-item>
                            <headless-menu-item>
                                <button
                                    type="button"
                                    data-request="onTailwindUISetTheme"
                                    data-request-data="dark_mode: 'dark'"
                                    data-request-success="updateColorScheme(data.dark_mode);"
                                    class="btn-darkmode"
                                    title="<?= e(trans('winter.tailwindui::lang.preferences.dark_mode.dark')) ?>">
                                    <i class="icon-moon"></i>
                                </button>
                            </headless-menu-item>
                        </div>
                        <?= Form::close() ?>
                    </div>
                <?php endif; ?>
                <div class="py-1">
                    <headless-menu-item>
                        <a
                            href="<?= Backend::url('backend/auth/signout') ?>"
                            class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 hover:no-underline dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-700">
                            <i class="icon-sign-out mr-2 text-sm text-gray-400 group-hover:text-gray-500 dark:group-hover:text-white text-center min-w-[1.25em]"></i>
                            <?php if (\BackendAuth::isImpersonator()) : ?>
                                <?= e(trans('backend::lang.account.stop_impersonating')) ?>
                            <?php else: ?>
                                <?= e(trans('backend::lang.account.sign_out')) ?>
                            <?php endif; ?>
                        </a>
                    </headless-menu-item>
                </div>
            </headless-menu-items>
        </transition>
    </headless-menu>
</div>