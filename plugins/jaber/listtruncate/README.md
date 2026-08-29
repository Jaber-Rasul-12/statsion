# About
Jaber plugin to add a toggleable switch as a column type to the backend.




## Usage

To add a switch column to a list; set the `type` of the column to `jaber-list-truncate`. 

Example:
```yaml
your_column:
    label: 'Your Label'
    # Define the type as "jaber-list-truncate" to enable this functionality
    type: jaber-list-truncate
    
    truncate: true 
    truncate_limit: 12
    show_more: true 
```


## Author

Jaber Rasul