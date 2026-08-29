# About
Jaber plugin to add a toggleable switch as a column type to the backend.



## Usage

To add a switch column to a list; set the `type` of the column to `jaber-list-switch`. 

Example:
```yaml
your_column:
    label: 'Your Label'
    # Define the type as "jaber-list-switch" to enable this functionality
    type: jaber-list-switch
    
    # Whether to use ✓/✗ icons to replace the Yes/No text (default: true)
    icon: true

    text-color: true  # this is for add class text-success and text-danger (default: true)
    font-size: 20  # this is for add font-size  (default: 16)
   
    # Overrides the title displayed on hover over the column
    titleTrue: 'Unpublish item' # (default: 'jaber.listswitch::lang.jaber.listswitch.title_true')
    titleFalse: 'Publish item' # (default: 'jaber.listswitch::lang.jaber.listswitch.title_false')
    
    # Overrides the text displayed on the button
    textTrue: 'Published' # (default: 'jaber.listswitch::lang.jaber.listswitch.text_true')
    textFalse: 'Unpublished' #(default: 'jaber.listswitch::lang.jaber.listswitch.text_false')
```


## Author

Jaber Rasul