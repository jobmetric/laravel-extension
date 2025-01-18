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
    ],

    "exceptions" => [
        "extension_already_installed" => "افزونه :extension/:name قبلاً نصب شده است.",
        "extension_not_installed" => "افزونه :extension/:name نصب نشده است.",
        "extension_folder_not_found" => "پوشه افزونه :extension/:name یافت نشد.",
        "extension_runner_not_found" => "Runner افزونه :extension/:name یافت نشد.",
        "extension_config_file_not_found" => "فایل پیکربندی افزونه :extension/:name یافت نشد.",
        "extension_configuration_not_match" => "پیکربندی افزونه :extension/:name با پیکربندی افزونه اصلی مطابقت ندارد.",
        "extension_class_name_not_match" => "نام کلاس افزونه :extension/:name با نام کلاس اصلی مطابقت ندارد.",
        "extension_dont_have_contract" => "افزونه :extension/:name دارای قرارداد JobMetric\Extension\Contracts\ExtensionContract نیست.",
        "extension_have_some_plugin" => "پسوند :extension دارای چند افزونه است. لطفا ابتدا افزونه را حذف نصب کنید.",
        "extension_type_invalid" => "نوع پسوند :extension/:name نامعتبر است.",
        "plugin_not_found" => "افزونه با شناسه :plugin_id پیدا نشد.",
        "plugin_not_multiple" => "پسوند :extension/:name دارای چندین افزونه نیست.",
    ],

    "messages" => [
        "extension" => [
            "installed" => "پسوند :extension/:name نصب شده است.",
            "uninstalled" => "پسوند :extension/:name حذف نصب شده است.",
            "updated" => "پسوند :extension/:name به روز شده است.",
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
