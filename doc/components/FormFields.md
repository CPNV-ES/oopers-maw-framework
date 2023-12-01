# Form fields

Here is list of framework built-in forms fields.

___
<!-- TOC -->
* [Form fields](#form-fields)
  * [General](#general)
  * [Text](#text)
    * [TextField](#textfield)
    * [TextAreaField](#textareafield)
  * [Special](#special)
    * [ChoiceField](#choicefield)
    * [EntityField](#entityfield)
<!-- TOC -->

## General

All field of any types have these options.

| Key          | Type          | Default  | Description                                                                                                   |
|--------------|:--------------|----------|---------------------------------------------------------------------------------------------------------------|
| `label`      | string\|array | Required | Label displayed before field in HTML view                                                                     |
| `attributes` | array         | `[name]` | List of HTML attributes that will be inserted in HTML tag of input (e.g. `<select>`, `<input>`, `<textarea>`) |


## Text

### TextField

Simple `<intput type="text">` that can have some options.

### TextAreaField

Simple `<textarea></textarea>` that can have some options.

## Special

### ChoiceField

Simple `<select></select>` that can have some options. To pass choices (`<option>`) use `choices` option key. To this array entry fill an array with `ChoicePram`. For ChoiceParam, you have to pass a value that will be saved in Entity and compared to auto select and a label displayed in form.

### EntityField

An EntityField use an Entity as field. In facts, if you have an Entity that have one or more entity in property use an EntityField to

| Key            | Type             | Default          | Description                                                                                                                                                                                                 |
|----------------|:-----------------|------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `entity_class` | string           | Required         | Data class used as field                                                                                                                                                                                    |
| `entity_value` | string           | Required         | Name of property of value to use as field in data class                                                                                                                                                     |
| `entity_label` | string           | Required         | Property to use as label for field (if your property is an object make sure to implement `_toString` method in it)                                                                                          |
| `entity_type`  | string\|Callable | TextField::class | Define which type to use. You can directly set an static value or pass a Callable to resolve it depending on the entity of field<br/>Callable need respect following signature `(object $entity) => string` |

Example of code to edit all categories of an article.

```php
$this
    ->add('categories', EntityField::class, [
        'label' => false,
        'entity_class' => Category::class,
        'entity_value' => 'name',
        'entity_type' => function (object $category) {
            if($category->isMultiline()) return \MVC\Form\Field\TextAreaField::class;
            return \MVC\Form\Field\TextField::class;
        }
        'entity_label' => false,
    ]);
```

