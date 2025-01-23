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

    "validation" => [
        "errors" => "خطاهای اعتبار سنجی رخ داده است.",
        "namespace_not_found" => "آدرس فایل :namespace یافت نشد.",
    ],

    "exceptions" => [
        "extension_already_installed" => "افزونه :name قبلاً نصب شده است.",
        "extension_not_installed" => "افزونه :name نصب نشده است.",
        "extension_not_uninstalled" => "افزونه :name حذف نصب نشده است.",
        "extension_not_deletable" => "افزونه :name قابل حذف نیست.",
        "extension_folder_not_found" => "پوشه افزونه :name یافت نشد.",
        "extension_runner_not_found" => "Runner افزونه :name یافت نشد.",
        "extension_config_file_not_found" => "فایل پیکربندی افزونه :name یافت نشد.",
        "extension_configuration_not_match" => "پیکربندی افزونه :name با پیکربندی افزونه اصلی مطابقت ندارد.",
        "extension_class_name_not_match" => "نام کلاس افزونه :name با نام کلاس اصلی مطابقت ندارد.",
        "extension_dont_have_contract" => "افزونه :name دارای قرارداد JobMetric\Extension\Contracts\ExtensionContract نیست.",
        "extension_have_some_plugin" => "افزونه :name دارای چند افزونه است. لطفا ابتدا افزونه را حذف نصب کنید.",
        "plugin_not_found" => "افزونه با شناسه :plugin_id پیدا نشد.",
        "plugin_not_multiple" => "افزونه :extension/:name دارای چندین افزونه نیست.",
    ],

    "messages" => [
        "extension" => [
            "installed" => "افزونه :name نصب شده است.",
            "uninstalled" => "افزونه :name حذف نصب شده است.",
            "deleted" => "افزونه :name حذف شده است.",
            "updated" => "افزونه :name به روز شده است.",
        ],

        "plugin" => [
            "added" => "پلاگین با موفقیت اضافه شد.",
            "edited" => "پلاگین با موفقیت ویرایش شد.",
            "deleted" => "پلاگین با موفقیت حذف شد.",
        ],
    ],

    "fields" => [
        "title" => [
            "label" => "عنوان",
            "info" => "عنوان پلاگین را وارد کنید.",
            "placeholder" => "عنوان پلاگین",
        ],
        "status" => [
            "label" => "وضعیت",
            "info" => "وضعیت افزونه را برای فعال یا غیرفعال کردن انتخاب کنید.",
        ]
    ],

    "list" => [
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
        ],
    ],

    "extension" => [
        "default_description" => "این یک افزونه تستی است. 😉",
    ],

];
