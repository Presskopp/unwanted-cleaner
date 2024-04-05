/* const test = '<h1> test</h1>';
document.addEventListener("DOMContentLoaded", function() {
document.getElementById('main_unwanted_cleaner').innerHTML= uwp_var.text.Unwanted_Cleaner_Settings;
}); */



let plugin_list_uwp =''
for (let i = 0; i < uwp_var.plugin_list.length; i++) {
   i==0 ? plugin_list_uwp = uwp_var.plugin_list[i] : plugin_list_uwp +=  '\n' + uwp_var.plugin_list[i];
}
const ui_page = `
<div class="wrap">
    <h2>${uwp_var.text.Unwanted_Cleaner_Settings}</h2>
    <div>
    <?php settings_fields('unwanted_plugins_group'); ?>
        <h3><?php echo esc_html__('List of unwanted plugins', 'unwanted-cleaner'); ?></h3>
        <h3>${uwp_var.text.List_of_unwanted_plugins}</h3>
        <p>${uwp_var.text.Enter_the_slugs_of_unwanted_plugins}</p>
        <textarea name="plugin-list-uwp" id="plugin-list-uwp" rows="5" cols="50">${plugin_list_uwp}</textarea>
        <button class="secondary" onClick="fun_handle_uwp('save')">${uwp_var.text.save_changes}</button>
    </div>
    <div>            
       
        <button class="secondary" onClick="fun_handle_uwp('delete')">${uwp_var.text.Delete_unwanted_plugins_now}</button>
    </div>
</div>
`


document.getElementById('main_unwanted_cleaner').innerHTML= ui_page;

function fun_delete_all_plugins_uwp(){
    console.log('fun_delete_all_plugins_uwp');
    var plugin_list = document.getElementById('plugin-list-uwp').value;
    console.log(plugin_list);
}
function fun_save_changes_uwp(){
    console.log('fun_save_changes_uwp');
    var plugin_list = document.getElementById('plugin-list-uwp').value;
    console.log(plugin_list);
}

function fun_handle_uwp(state){
    console.log('fun_handle_uwp');
    console.log(uwp_var.ajaxurl);
    const plugin_list = document.getElementById('plugin-list-uwp').value;
    console.log(plugin_list);
    if(state == 'delete'){
       console.log('delete')
    }else if(state == 'save'){
        console.log('save')
    }


        
        data = {};
  
        jQuery(function ($) {
      
          
            data = {
                action: "handler_uwp",
                state: state,
                plugin_list: plugin_list,
                nonce: uwp_var.nonce
              };
      
          $.post(uwp_var.ajaxurl, data, function (res) {
                console.log(res);
            /* if (res.data.r == "insert") {
              if (res.data.value && res.data.success == true) {
                state_check_ws_p = 0;
                form_ID_emsFormBuilder = parseInt(res.data.id)
      
                show_message_result_form_set_EFB(1, res.data.value)
              } else {
                alert(res, "error")
                show_message_result_form_set_EFB(0, res.data.value, `${efb_var.text.somethingWentWrongPleaseRefresh}, Code:400-1`)
              }
            } else if (res.data.r == "update" || res.data.r == "updated" && res.data.success == true) {
              show_message_result_form_set_EFB(2, res.data.value)
            } else {
              if (res.data.m == null || res.data.m.length > 1) {
      
                show_message_result_form_set_EFB(0, res.data.value, `${efb_var.text.somethingWentWrongPleaseRefresh}, Code:400-400`)
              } else {
                show_message_result_form_set_EFB(0, res.data.value, `${res.data.m}, Code:400-400`)
              }
            }  */
          })
          return true;
        });
      
      }
