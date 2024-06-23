document.addEventListener("DOMContentLoaded", function() {
    
    const pluginListUWP = uncl_var.plugin_list;
    let pluginlist = null;
    for (let key in pluginListUWP) {
        if (Object.prototype.hasOwnProperty.call(pluginListUWP, key)) {
            if (typeof pluginListUWP[key] === 'string') {
                pluginlist === null ? pluginlist = pluginListUWP[key].replace(/,/g, '\n') : pluginlist += '\n'+ pluginListUWP[key].replace(/,/g, '\n')
            }
        }
    }
    
    const delete_ok= uncl_var.delete_ok=='true' || uncl_var.delete_ok==1 ? 1 : 0
    const ui_page = `
    <div class="wrap">
        <h2>${uncl_var.text.Unwanted_Cleaner_Settings}</h2>
        <div id="noti-uncl"> </div>
        <div>
            <h3>${uncl_var.text.List_of_unwanted_plugins}</h3>
            <p>${uncl_var.text.Enter_the_slugs_of_unwanted_plugins}</p>
            <p>${uncl_var.text.They_will_be_automatically_deleted}</p>
            <textarea name="plugin-list-uncl" id="plugin-list-uncl" rows="5" cols="50">${pluginlist}</textarea>
        </div>
        <div>
            <input type="checkbox" id="delete_ok" name="delete_ok" value="0" ${delete_ok ? 'checked' : ''}>
            <label for="delete_ok">If checked you give permission to Unwanted Cleaner to <b>automatically</b> delete the plugins listed above, whenever a core update did run.</label><br><br>
        </div>
        <div>
            <button class="button button-primary" id="saveButton">${uncl_var.text.save_changes}</button>
        </div>
        <br>
        <div>
        <p>${uncl_var.text.delete_now_hint}</p>
            <button class="button" id="deleteButton">${uncl_var.text.delete_unwanted_plugins}</button>
        </div>
    </div>
    `;
    document.getElementById('main_unwanted_cleaner').innerHTML = ui_page;

    setTimeout(() => {
        // Event-Handler for save button
        document.getElementById('saveButton').addEventListener('click', function() {
            fun_handle_uwp('save');
        });
    
        // Event-Handler for delete now button
        document.getElementById('deleteButton').addEventListener('click', function() {
            fun_handle_uwp('delete');
        });        
    }, 100);
});

function fun_handle_uwp(state){
    const plugin_list = document.getElementById('plugin-list-uncl').value;
    const checkbox_delete = document.getElementById('delete_ok').checked;
    noti_box = (m, clss) => {
        let noti = document.getElementById('noti-uncl')
        // noti.className = clss;
        noti.innerHTML = '<div class="notice notice-' + clss + ' ' + '" data-slug="unwanted-changer"><p>' + m + '</p></div>';
        el.innerHTML = d;
    }
    let el = document.getElementById(`${state}Button`);
    const d = el.innerHTML;
    el.blur();
    if(state == 'save'){
        el.innerHTML=uncl_var.text.saving;
    }else if(state == 'delete'){
        el.innerHTML=uncl_var.text.deleting;
    }

    data = {};
    let msg = 'not working';
    let clss = "not-valid-";
    jQuery(function ($) {
    
        data = {
            action: "uncl_handler",
            state: state,
            plugin_list: plugin_list,
            delete_ok: checkbox_delete,
            nonce: uncl_var.nonce
            };
            
        $.post(uncl_var.ajaxurl, data, function (res) {
            if(res.data.success == true){
                msg = res.data.m;
                // clss= "valid";
                clss= "success";
                noti_box(msg ,clss);
            } else {
                msg = res.data.m;
                // clss = "not-valid";
                clss = "error";
                noti_box(msg ,clss);
            }  
        })
        return true;
    });
}
