<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base Extension Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during Extension for
    | various messages that we need to display to the user.
    |
    */

    'entity_names' => [
        'extension' => 'افزونه',
        'plugin' => 'پلاگین',
    ],

    "validation" => [
        "namespace_not_found" => "آدرس فایل :namespace یافت نشد.",
    ],

    "exceptions" => [
        "extension_already_installed" => "افزونه :name قبلاً نصب شده است.",
        "extension_not_installed" => "افزونه :name نصب نشده است.",
        "extension_not_uninstalled" => "افزونه :name حذف نصب نشده است.",
        "extension_not_deletable" => "افزونه :name قابل حذف نیست.",
        "extension_from_package_not_deletable" => "افزونه :name از طریق پکیج ارائه شده و از اینجا قابل حذف نیست.",
        "extension_folder_not_found" => "پوشه افزونه :name یافت نشد.",
        "extension_runner_not_found" => "Runner افزونه :name یافت نشد.",
        "extension_config_file_not_found" => "فایل پیکربندی افزونه :name یافت نشد.",
        "extension_configuration_not_match" => "پیکربندی افزونه :name با پیکربندی افزونه اصلی مطابقت ندارد.",
        "extension_class_name_not_match" => "نام کلاس افزونه :name با نام کلاس اصلی مطابقت ندارد.",
        "extension_dont_have_contract" => "افزونه :name از AbstractExtension ارث‌بری نمی‌کند.",
        "extension_have_some_plugin" => "افزونه :name دارای چند افزونه است. لطفا ابتدا پلاگین‌ها را حذف کنید.",
        "extension_not_found" => "افزونه یافت نشد.",
        "plugin_not_found" => "افزونه با شناسه :plugin_id پیدا نشد.",
        "plugin_not_multiple" => "افزونه :extension/:name دارای چندین افزونه نیست.",
        "plugin_not_match_extension" => "پلاگین :plugin_id با افزونه :extension_id مطابقت ندارد.",
    ],

    "messages" => [
        "extension" => [
            "installed" => "افزونه :name نصب شده است.",
            "uninstalled" => "افزونه :name حذف نصب شده است.",
            "deleted" => "افزونه :name حذف شده است.",
            "upgraded" => "افزونه :name ارتقا داده شده است.",
        ],

        "plugin" => [
            "stored" => "پلاگین با موفقیت ذخیره شد.",
            "updated" => "پلاگین با موفقیت به روز شد.",
            "added" => "پلاگین با موفقیت اضافه شد.",
            "edited" => "پلاگین با موفقیت ویرایش شد.",
            "deleted" => "پلاگین با موفقیت حذف شد.",
        ],
    ],

    "list" => [
        "extension" => [
            "message" => [
                "confirm" => [
                    "uninstall" => "آیا از حذف نصب افزونه اطمینان دارید؟",
                    "delete" => "آیا از حذف فایل های افزونه اطمینان دارید؟ این گزینه برگشت ناپذیر است.",
                ],
            ],
            "columns" => [
                "name" => "نام افزونه",
                "version" => "نسخه",
                "author" => "نویسنده",
                "installed_at" => "تاریخ نصب",
                "website" => "وب سایت",
                "email" => "ایمیل",
                "namespace" => "آدرس فایل افزونه",
                "license" => "مجوز",
                "delete_note" => "توجه: حذف افزونه باعث حذف تمامی پلاگین های مربوط به آن می شود.",
                "delete" => "حذف افزونه",
                "creation_at" => "تاریخ ساخت افزونه",
                "updated_at" => "تاریخ آخرین بروزرسانی",
                "not_installed" => "نصب نشده",
                "simple" => "ساده",
                "multiple" => "چندگانه",
            ],
            "buttons" => [
                "install" => "نصب",
                "uninstall" => "حذف نصب",
                "add_plugin" => "افزودن پلاگین",
                "plugin_list" => "لیست پلاگین ها",
                "are_you_sure_to_uninstall" => "بله، حذف نصب کن",
                "are_you_sure_to_delete" => "بله، پاک کن بره",
            ],
        ],
        "plugin" => [
            "label" => "پلاگین‌های افزونه :name",
            "description" => "در این قسمت می‌توانید پلاگین‌های افزونه :name را مدیریت کنید.",
            "filters" => [
                "name" => [
                    "title" => "نام پلاگین",
                    "placeholder" => "جستجو بر اساس نام پلاگین",
                ]
            ],
        ],
    ],

    "form" => [
        "plugin" => [
            "create" => [
                "title" => "ایجاد پلاگین برای افزونه :name",
            ],
            "edit" => [
                "title" => "ویرایش پلاگین شماره :number از افزونه :name",
            ],
            "fields" => [
                "name" => [
                    "title" => "نام پلاگین",
                    "placeholder" => "نام پلاگین را وارد کنید",
                ],
            ],
        ],
    ],

    "extension" => [
        "default_description" => "این یک افزونه تستی است. 😉",
    ],

    "types" => [
        "module" => [
            "label" => "ماژول",
            "description" => "افزونه‌هایی که ماژول و قابلیت به سیستم اضافه می‌کنند.",
        ],
    ],

    "events" => [
        "extension" => [
            "group" => "افزونه",
            "installed" => [
                "title" => "افزونه نصب شد",
                "description" => "این رویداد هنگام نصب یک افزونه فراخوانی می‌شود.",
            ],
            "uninstalled" => [
                "title" => "افزونه حذف نصب شد",
                "description" => "این رویداد هنگام حذف نصب یک افزونه فراخوانی می‌شود.",
            ],
            "deleted" => [
                "title" => "افزونه حذف شد",
                "description" => "این رویداد هنگام حذف فایل‌های افزونه از دیسک فراخوانی می‌شود.",
            ],
            "migrations_run" => [
                "title" => "اجرای مایگریشن‌های افزونه",
                "description" => "این رویداد پس از اجرای مایگریشن‌های افزونه فراخوانی می‌شود.",
            ],
            "migrations_rollback" => [
                "title" => "بازگردانی مایگریشن‌های افزونه",
                "description" => "این رویداد پس از بازگردانی مایگریشن‌های افزونه فراخوانی می‌شود.",
            ],
        ],
        "plugin" => [
            "group" => "پلاگین",
            "stored" => [
                "title" => "پلاگین ذخیره شد",
                "description" => "این رویداد هنگام ذخیره یک پلاگین فراخوانی می‌شود.",
            ],
            "deleted" => [
                "title" => "پلاگین حذف شد",
                "description" => "این رویداد هنگام حذف یک پلاگین فراخوانی می‌شود.",
            ],
            "updated" => [
                "title" => "پلاگین به‌روزرسانی شد",
                "description" => "این رویداد هنگام به‌روزرسانی یک پلاگین فراخوانی می‌شود.",
            ],
            "added" => [
                "title" => "پلاگین اضافه شد",
                "description" => "این رویداد هنگام اضافه شدن یک پلاگین فراخوانی می‌شود.",
            ],
            "edited" => [
                "title" => "پلاگین ویرایش شد",
                "description" => "این رویداد هنگام ویرایش یک پلاگین فراخوانی می‌شود.",
            ],
        ],
        "kernel" => [
            "group" => "هسته افزونه",
            "extensions_discovered" => [
                "title" => "افزونه‌ها کشف شدند",
                "description" => "پس از کشف کلاس‌های افزونه از فایل‌سیستم فراخوانی می‌شود.",
            ],
            "extensions_loaded" => [
                "title" => "افزونه‌ها بارگذاری شدند",
                "description" => "پس از بارگذاری افزونه‌های نصب‌شده از دیتابیس فراخوانی می‌شود.",
            ],
            "registering" => [
                "title" => "در حال ثبت",
                "description" => "قبل از فاز ثبت افزونه‌ها فراخوانی می‌شود.",
            ],
            "registered" => [
                "title" => "ثبت شد",
                "description" => "پس از فاز ثبت افزونه‌ها فراخوانی می‌شود.",
            ],
            "booting" => [
                "title" => "در حال بوت",
                "description" => "قبل از فاز بوت افزونه‌ها فراخوانی می‌شود.",
            ],
            "booted" => [
                "title" => "بوت شد",
                "description" => "پس از فاز بوت افزونه‌ها فراخوانی می‌شود.",
            ],
        ],
    ],

];
