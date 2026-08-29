<?php namespace {namespace};

use Model;
// use Winter\Storm\Database\Builder;
// use BackendAuth;
/**
 * Model
 */
class {classname} extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    
    // use \Winter\Storm\Database\Traits\Revisionable;
    /**
     * Enables model revision tracking.
     *
     * - Uses the `Revisionable` trait from `Winter\Storm\Database\Traits` to track changes to model attributes.
     * - Tracks changes for the `name` attribute, allowing you to view revisions of this field.
     * - Limits the number of revisions stored to a maximum of 5 for this model.
     *
     * @var int
     */
    // public $revisionableLimit = 5;
    
    /**
     * Defines the attributes that are revisionable.
     *
     * - Specifies which attributes should be tracked for revisions.
     * - In this case, the `name` attribute is revisionable.
     *
     * @var array
     */
    // protected $revisionable = ['name'];
    // On Column 
    // store-list-revision-history:
    //     label: old_changes
    //     span: auto
    //     permissions: old_changes
    //     type: store-list-revision-history
    //     searchable: false
    //     sortable: false

    /**
     * Implements translation functionality for the model.
     *
     * - Uses the custom `TranslateBackend` class to handle backend localization.
     * - Implements the `TranslatableModel` behavior from WinterCMS to support 
     *   multilingual fields.
     *
     * @package Store\Backendlocalization
    */
    
    // use \Store\Backendlocalization\Class\TranslateBackend;
     
    // public $implement = ['Winter.Translate.Behaviors.TranslatableModel'];
     
    // public $translatable = ['name'];


    
    {dynamicContents}

    /**
      * Booted model event handler.
      *
      * This method is executed when the model is initialized. It applies a global 
      * scope named 'nameScope' to modify queries based on specific conditions. 
      * The scope can be used to filter or modify queries dynamically depending 
      * on the authenticated backend user.
      *
      * @return void
    */
    // protected static function booted()
    // {
    //     $user = BackendAuth::getUser();
    // 
    //     static::addGlobalScope('nameScope', function (Builder $builder) use ($user) {
    //         // Define global query modifications here
    //     });
    // }



    /**
     * @var string The database table used by the model.
     */
    public $table = '{table}';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    /**
    * this is example for rule 
    * public $rules = [
    *     'name'         => 'required|string|max:255',
    *     'uuid'         => 'nullable|uuid',
    *     'level'        => 'required|in:damaged,basic',
    *     'warehouse_id' => 'required|exists:store_warehouses_warehouses,id',
    *     'user_id' => 'nullable|exists:backend_users,id',
    *     'status' => 'nullable|boolean',
    * 
    * ]; 
    */

    /**
     * Defines a "belongsTo" relationship.
     *
     * - Establishes a relationship between this model and the `nameClass` model.
     * - The foreign key `key_relation_id` is used to link the related model.
     * - This allows accessing the related `namerelation` data conveniently.
     *
     * @var array
     */
    // public $belongsTo = [
    //     'namerelation' => [nameClass::class, 'key' => 'key_relation_id'],
    // ];


    /**
     * Defines a "hasMany" relationship.
     *
     * - Establishes a one-to-many relationship between this model and the `nameClass` model.
     * - The foreign key `key_relation_id` is used to link multiple related records.
     * - This allows retrieving multiple `nameRelation` records associated with this model.
     *
     * @var array
     */
    // public $hasMany = [
    //     'nameRelation' => [nameClass::class, 'key' => 'key_relation_id'],
    // ];

    /**
     * Defines a "morphMany" polymorphic relationship.
     *
     * - Establishes a polymorphic one-to-many relationship with the `Note` model.
     * - The `name` attribute is set to 'target', allowing this model to be associated 
     *   with multiple notes dynamically.
     * - This allows retrieving all related `notes` for this model.
     *
     * @var array
     */
    // public $morphMany = [
    //     'notes' => [\Winter\Notes\Models\Note::class, 'name' => 'target'],
    //     'revision_history' => ['System\Models\Revision', 'name' => 'revisionable']
    // ];

     /**
     * @var array Attach One relationship for file attachment
     */
    // public $attachOne = [
    //     'poster' => File::class,
    // ];
    // public $attachMany = [
    //     'poster' => File::class,
    // ];

    /**
     * Specifies attributes that should be stored as JSON.
     *
     * - The 'information' attribute is stored as a JSON-encoded string in the database.
     *
     * @var array
     */
    // protected $jsonable = ['information'];
    
    /**
     * Defines attribute type casting.
     *
     * - The 'information' attribute is cast to an array for easy manipulation.
     *
     * @var array
     */
    // protected $casts = [
    //     'information' => 'array',
    // ];
    
    /**
     * Get the formatted key-value pairs from the 'information' attribute.
     *
     * - Retrieves key-value pairs from the 'information' attribute of the model.
     * - Formats each key-value pair as a string using '::' as a separator.
     * - Joins all formatted pairs into a single string separated by commas.
     *
     * @return string Formatted key-value pairs as a string.
     */
    // public function getInformationKeyValueAttribute()
    // {
    //     $informationArray = $this->information;
    // 
    //     // Ensure $informationArray is an array
    //     if (!is_array($informationArray)) {
    //         $informationArray = [];
    //     }
    // 
    //     $keyValuePairs = collect($informationArray)->map(function ($item) {
    //         return $item['key'] . ' :: ' . $item['value'];
    //     });
    // 
    //     return $keyValuePairs->implode(', ');
    // }

    // on Column 
    // information_key_value:
    //     label: information
    //     type: text
    //     searchable: false
    //     sortable: false
    // on field
    // information:
    //         label: information
    //         prompt: add_new_item
    //         style: default
    //         span: auto
    //         type: repeater
    //         form:
    //             fields:
    //                 key:
    //                     label: key
    //                     span: auto
    //                     type: text
    //                 value:
    //                     label: value
    //                     span: auto
    //                     type: text
    
    /**
     * Get dropdown options for a specified field.
     *
     * - This method is used to provide dropdown options dynamically.
     * - It accepts the field name, current value, and form data as parameters.
     * - Returns an array of options that can be used in dropdown selections.
     *
     * @param  string  $fieldName The name of the field for which options are retrieved.
     * @param  mixed   $value The current value of the field.
     * @param  array   $formData The form data available for context.
     * @return array   An array of dropdown options.
     */
    // public function getDropdownOptions($fieldName, $value, $formData)
    // {
    //     return [];
    // }


    /**
     * Perform actions before deleting 
     *
     * @throws \ValidationException
     */
    // public function beforeDelete()
    // {
    // 
    //     // Check if there are associated records in any of the hasMany relationships
    //     foreach ($this->hasMany as $relation => $details) {
    //         if ($this->{$relation}->count() > 0) {
    //             // If associated records exist, prevent deletion and throw a validation exception
    //             throw new \ValidationException(['name' => trans('author.plugin::lang.plugin.message_delete')]);
    //             message en 
    //                     'message_delete' => 'Cannot be deleted because there is data associated with the section',
    //         message ar  
    //                     'message_delete' => 'لا يمكن الحذف لأن هناك بيانات مرتبطة بالقسم',
    //         message ku 
    //                     'message_delete' => 'Ji ber ku hîn cîhnamek tê de tê deynakir nabe ku bibin jêbirin',
    //     
    //         
    //         }
    //     }
    // }

    // before the model is saved, when first created.
    // public fucntion beforeCreate(){
    //     
    // }	
    //after the model is saved, when first created.
    // public fucntion afterCreate(){
    // 
    // }	
    // before the model is saved, either created or updated.
    // public fucntion beforeSave(){
    // 
    // }	
    // after the model is saved, either created or updated.
    // public fucntion afterSave(){
    // 
    // }	
    // before the supplied model data is validated.
    // public fucntion beforeValidate(){
    // 
    // }	
    // after the supplied model data has been validated.
    // public fucntion afterValidate(){
    // 
    // }	
    // before an existing model is saved.
    // public fucntion beforeUpdate(){
    // 
    // }	
    // after an existing model is saved.
    //public fucntion afterUpdate(){
    //
    //}		
    // after an existing model is deleted.
    // public fucntion afterDelete(){
    // 
    // }	
    // before a soft-deleted model is restored.
    // public fucntion beforeRestore(){
    // 
    // }	
    // after a soft-deleted model has been restored.
    // public fucntion afterRestore(){
    // 
    // }	
    // before an existing model is populated.
    // public fucntion beforeFetch(){
    // 
    // }	
    // after an existing model has been populated.
    // public fucntion afterFetch(){
    // 
    // }	



}
