# Form component

___
<!-- TOC -->
* [Form component](#form-component)
  * [Usage](#usage)
    * [Form class creation](#form-class-creation)
    * [In Controllers](#in-controllers)
    * [In Views](#in-views)
  * [Future features](#future-features)
<!-- TOC -->


## Usage
___

The Form component is a tool to help you solve the problem of allowing end-users to interact with the data and modify the data in your application. And though traditionally this has been through HTML forms, the component focuses on processing data to and from your client and application, whether that data be from a normal form post or from an API.

The recommended workflow when working with Symfony forms is the following:

- **Build the form** in a Symfony controller or using a dedicated form class;
- **Render the form** in a template so the user can edit and submit it;
- Process the form to validate the submitted data, transform it into PHP data and do something with it (e.g. persist it in a database).

### Form class creation

First you need to have a data class. Your data class **MUST** implement for each editable properties getter and setter under following format `getTitle` and `setTitle`. In addition, you MUST set a default it can be an empty string or only null.

Next you can create your form class. To do so start by create a form that extend form `AbstractForm` then implement `_construct` method and class parent constructor by calling `parent::__construct`. Still in the constructor use `$this->addOption` method to add the option with key `'view_template'` and for value the path to your form template.

Then implement `buildForm` method and in this method start declaring your fields. To do so, using `add` method

Here is an example of form class.

```php
// src/Form/ProductForm.php
class ProductForm extends AbstractForm
{

    public function __construct(object $entity)
    {
        parent::__construct($entity);
        $this->addOption('view_template', 'product/form');
        $this->buildForm();
    }

    public function buildForm(): void
    {
        $this->add('name', TextField::class);
    }
}
```

See framework built-in field types in [FormFields.md](FormFields.md)

### In Controllers

```php
// src/Controller/UserController.php
public function new(): Response
{
    $product = new Product();
    $form = new ProductForm($product);
    $form->handleRequest($this->request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Make what you want with $product that have been automatically update with form data
    }
    // ...
}
```

### In Views

```php
// views/product/form.php
<?= $this->form->start() ?>
    <?= $this->form->fields() ?> Will Automatically render all fields
    <button type="submit">Save</button>
<?= $this->form->end() ?>
```

--- OR ---

```php
// views/product/form.php
<?= $this->form->start() ?>
    <?= $this->form->name ?>
    <?= $this->form->price ?>
    <button type="submit">Save</button>
<?= $this->form->end() ?>
```

## Future features

- [ ] Add support of CSRF token
- [x] Make EntityField able to determine the field type it self