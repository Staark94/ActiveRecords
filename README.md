# ActiveRecords
PDO Snippet Active Records


# Eloquent Model Functions

### Eloquent Model Usage

``class Model extends \ActiveRecord\Eloquent {}``

### Eloquent Set Connection
``\ActiveRecord\Eloquent::setConnection('host', '\dbname', 'user', '\pass');``

### Model store methods
``function save();``

``function delete();``

``function insert();``

``function update();``

### Model Functions
``Model::find(int $id);``

``Model::findAll();``

``Model::findById(int $id);``

``Model::findByEmail(string $email);``
