# Lara-Service

Generalized Service layer

## Installation

Run the following command from you terminal:


 ```bash
 composer require "omnicode/lara-service: 2.0.*"
 ```

or add this to require section in your composer.json file:

 ```
 "omnicode/lara-service": "2.0.*"
 ```

then run ```composer update```


## Usage

First, create your Service class like shown below with example `AcccountService`

```php
<?php

namespace App\Services;

use App\Repositories\Contracts\AccountRepositoryInterface as AccountRepository;
use App\Validators\AccountValidator;
use LaraService\Services\LaraService;

class AccountService extends LaraService
{
    public function __construct(AccountRepository $accountRepository, AccountValidator $accountValidator)
    {
        $this->baseRepository = $accountRepository;
        $this->baseValidator = $accountValidator;
    }
}

```

The Repository and Validator classes should have the following methods `pushCriteria, saveAssociated, findForShow, findFillable, getKeyName, destroy, getIndexableColumns, paginate, setSortingOptions` and `isValid, getErrors` respectively. Those are already implemented in [Lara Validation](https://github.com/omnicode/lara-validation) and [Lara Repository](https://github.com/omnicode/lara-repo) packages.


 
And finally, use the service in the controller:

```php
<?php

namespace App\Http\Controllers;

use App\Services\AccountService;

class AccountsController extends Controller
{   
    public function __construct(AccountService $accountService)
    {
        parent::__construct();
        $this->baseService = $accountService;
    }
}

```



## Available Methods

The following methods are available in LaraService:

##### LaraRepo\Contracts\RepositoryInterface

```php
    public function setBaseRepository(RepositoryInterface $repository)
    public function getBaseRepository()
    public function setBaseValidator($validator)
    public function getBaseValidator ()
    public function setValidationErrors($errors)
    public function getValidationErrors()
    public function paginate($sort = [], $group = self::GROUP)
    public function create($data)
    public function createWithRelations($data, $relations = null)
    public function findForShow($id, $columns = null)
    public function find($id)
    public function update($id, $data)
    public function updateWithRelations($id, $data, $relations = null)
    public function destroy($id)
    public function validate($validator, $data, $options = [])
    public function paginateRepositoryWhere($repository, $group = self::GROUP, $column = null, $val = null)
    public function paginateRepository($repository, $group = self::GROUP)
    public function setSortingOptions($repository, $options = [], $group = self::GROUP)
}
```

Example - create a new account repository:

```php
    
    // for index page use
    // returns item list and columns with their columns information 
    $this->accountService->paginate()
      
    // if you want to sort your index page use
    $this->accountService->setSortingOptions($repository)
    
    // to create new record
    $this->accountService->create($request->all());
    
    // returns validation errors of last operation
    $this->accountService->getValidationErrors();
    
    // to save items with relations
    $this->accountService->createWithRelations($data, $relations)
    
    // to find based on $showable attributes
    $this->accountService->findForShow($id)
    
    // usual find with fillable columns
    $this->accountService->find($id)
    
    // to update based on primary key
    $this->accountService->update($id, $data)

    // to update and item with relations
    $this->accountService->updateWithRelations($id, $data, $relations)
    
    // to delelte
    $this->accountService->destroy($id)
    
    // to validate the data (internally uses the injected Validator)
    $this->accountService->validate($validator, $data, $options = [])
```
