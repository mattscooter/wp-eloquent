<?php
namespace WeDevs\ORM\Eloquent;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Str as Str;
use Rakit\Validation\Validator;

/**
 * Model Class
 *
 * @package WeDevs\ERP\Framework
 */
abstract class Model extends Eloquent {
    
    protected $rules=[];

    protected $errors;

    public function validate()
    {
        $validator = new Validator;
        $validation = $validator->make($this->attributes, $this->rules);
        $validation->validate();

        // check for failure
        if ($validation->fails())
        {
            // set errors and return false
            $this->errors = $validation->errors;
            return false;
        }

        // validation pass
        return true;
    }

    public function errors()
    {
        return $this->errors;
    }

    /**
     * @param array $attributes
     */
    public function __construct( array $attributes = array() ) {
        static::$resolver = new Resolver();

        parent::__construct( $attributes );
    }

    /**
     * Get the database connection for the model.
     *
     * @return Database
     */
    public function getConnection() {
        return Database::instance();
    }

    /**
     * Get the table associated with the model.
     *
     * Append the WordPress table prefix with the table name if
     * no table name is provided
     *
     * @return string
     */
    public function getTable() {
        if ( isset( $this->table ) ) {
            return $this->table;
        }

        $table = Str::of(str_replace( '\\', '', Str::plural( class_basename( $this ) ) ))->snake() ;

        return $this->getConnection()->db->prefix . $table ;
    }

    /**
     * Get a new query builder instance for the connection.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newBaseQueryBuilder() {

        $connection = $this->getConnection();

        return new Builder(
            $connection, $connection->getQueryGrammar(), $connection->getPostProcessor()
        );
    }

    /**
     * Override the built in save method to perform validation if there are any validation 
     * rules defined.  Returns false if method fails.  Otherwise returns result from parent.
     * 
     * Accepts a second parameter validate which can be used to skip validation.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function save(array $options = [], $validate=true){
        if ($validate && count($this->rules) > 0) {
            if ($this->validate()) {
                return parent::save($options);
            } else {
                return false;
            }
        } else {
            return parent::save($options);
        }
    }
}
