[Back To README.md](https://github.com/jobmetric/laravel-extension/blob/master/README.md)

# Introduction to Fields Extension

What are the fields and what should be done with them?

Why should fields be used at all?

What happens when we define a field?

We are going to review the fields here and express our expectations from them in the form of this document.

### What are the fields for?

The fields are used to define the variable values of the plugins, and we can use them to define the extensions, for example, we can consider the `width` and `height` fields for the Banner extension that we have given before, which is the banner we want in Let's show the website in what size and size it should be displayed, and we can provide this topic in the form of a dynamic field to the administrator so that he can adjust it himself.

### Why should fields be used at all?

The fields are used to create variable values for our extension, and we can dynamically change these items in the program based on each plugin.

### How to define a field?

To define the field, we must first define the extension and then define the fields inside the extension, which can be expressed by the following objects of different shapes.

> Remember that when you get the output from the plugin fields, the two standard fields that are `title` and `status` are always displayed in the output of the plugins, so please don't be surprised and welcome it.

## Types of fields

### Text

```json
{
    "name": "title",
    "type": "text",
    "required": true,
    "default": "Title",
    "label": "Title",
    "info": "The title of the banner",
    "placeholder": "Enter the title of the banner",
    "validation": "required|min:3|max:100"
},
```

### Number

```json
{
    "name": "width",
    "type": "number",
    "required": true,
    "default": 100,
    "label": "Width",
    "info": "The width of the banner",
    "placeholder": "Enter the width of the banner",
    "validation": "numeric|min:1|max:1000"
},
```

### Textarea

```json
{
    "name": "description",
    "type": "textarea",
    "required": true,
    "default": "Description",
    "label": "Description",
    "info": "The description of the banner",
    "placeholder": "Enter the description of the banner",
    "validation": "required|min:3|max:1000"
},
```

### Select

```json
{
    "name": "status",
    "type": "select",
    "required": true,
    "default": "active",
    "label": "Status",
    "info": "The status of the banner",
    "validation": "required",
    "options": [
        {
            "value": "active",
            "label": "Active"
        },
        {
            "value": "inactive",
            "label": "Inactive"
        }
    ]
},
```

### Checkbox

```json
{
    "name": "status",
    "type": "checkbox",
    "required": true,
    "default": "active",
    "label": "Status",
    "info": "The status of the banner",
    "validation": "required",
    "options": [
        {
            "value": "active",
            "label": "Active"
        },
        {
            "value": "inactive",
            "label": "Inactive"
        }
    ]
},
```

### Radio

```json
{
    "name": "status",
    "type": "radio",
    "required": true,
    "default": "active",
    "label": "Status",
    "info": "The status of the banner",
    "validation": "required",
    "options": [
        {
            "value": "active",
            "label": "Active"
        },
        {
            "value": "inactive",
            "label": "Inactive"
        }
    ]
},
```

### Date

```json
{
    "name": "date",
    "type": "date",
    "required": true,
    "default": "2021-06-27",
    "label": "Date",
    "info": "The date of the banner",
    "placeholder": "Select the date of the banner",
    "validation": "required|date"
},
```

### Time

```json
{
    "name": "time",
    "type": "time",
    "required": true,
    "default": "18:43:17",
    "label": "Time",
    "info": "The time of the banner",
    "placeholder": "Select the time of the banner",
    "validation": "required|time"
},
```

### Datetime

```json
{
    "name": "datetime",
    "type": "datetime",
    "required": true,
    "default": "2021-06-27 18:43:17",
    "label": "Datetime",
    "info": "The datetime of the banner",
    "placeholder": "Select the datetime of the banner",
    "validation": "required|date"
},
```

### Color

```json
{
    "name": "color",
    "type": "color",
    "required": true,
    "default": "#000000",
    "label": "Color",
    "info": "The color of the banner",
    "placeholder": "Select the color of the banner",
    "validation": "required|color"
},
```

### Email

```json
{
    "name": "email",
    "type": "email",
    "required": true,
    "default": "",
    "label": "Email",
    "info": "The email of the banner",
    "placeholder": "Enter the email of the banner",
    "validation": "required|email"
},
```

### Url

```json
{
    "name": "url",
    "type": "url",
    "required": true,
    "default": "",
    "label": "Url",
    "info": "The url of the banner",
    "placeholder": "Enter the url of the banner",
    "validation": "required|url"
},
```

### Password

```json
{
    "name": "password",
    "type": "password",
    "required": true,
    "default": "",
    "label": "Password",
    "info": "The password of the banner",
    "placeholder": "Enter the password of the banner",
    "validation": "required|min:6|max:100"
},
```

### Hidden

```json
{
    "name": "id",
    "type": "hidden",
    "required": true,
    "default": 1
},
```

### Switch

```json
{
    "name": "status",
    "type": "switch",
    "required": true,
    "default": true,
    "label": "Status",
    "info": "The status of the banner",
    "options": [
        {
            "value": true,
            "label": "Active"
        },
        {
            "value": false,
            "label": "Inactive"
        }
    ]
}
```

## What do these values do?

| Field         | Description                                                                                                                                                                                                                   |
|---------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `name`        | The `name` value is used to define the name of the field in the program, and after saving the value in the plugin, it is returned with the same name. In addition, `the same name should not be used twice in each extension` |
| `type`        | The `type` value is used to define the type of field, and the type of field can be selected from the list of fields that we have defined above.                                                                               |
| `required`    | The `required` value is used to define whether the field is required or not, and if the field is required, the user must enter a value for it.                                                                                |
| `default`     | The `default` value is used to define the default value of the field, and if the user does not enter a value for the field, the default value is used.                                                                        |
| `label`       | The `label` value is used to define the label of the field, and the label is displayed to the user to understand what the field is for.                                                                                       |
| `info`        | The `info` value is used to define the information of the field, and the information is displayed to the user to understand what the field is for.                                                                            |
| `placeholder` | The `placeholder` value is used to define the placeholder of the field, and the placeholder is displayed to the user to understand what the field is for.                                                                     |
| `validation`  | The `validation` value is used to define the validity of the field and the validation is used to verify the value entered by the user, `which you can use standard validations.`                                              |
| `options`     | The `options` value to define field options and options to define values that the user can select.                                                                                                                            |

- [Next To Plugin](https://github.com/jobmetric/laravel-extension/blob/master/docs/plugin.md)
