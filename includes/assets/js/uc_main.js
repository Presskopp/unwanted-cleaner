document.addEventListener("DOMContentLoaded", function() {
    
    const pluginListUWP = uwp_var.plugin_list;
    let pluginlist=null;
    for (let key in pluginListUWP) {
        if (Object.prototype.hasOwnProperty.call(pluginListUWP, key)) {
            if (typeof pluginListUWP[key] === 'string') {
                pluginlist===null ?  pluginlist= pluginListUWP[key].replace(/,/g, '\n') : pluginlist += '\n'+ pluginListUWP[key].replace(/,/g, '\n')
            }
        }
    }

    const ui_page = `
    <div class="wrap">
        <h2>${uwp_var.text.Unwanted_Cleaner_Settings}</h2>
        <div id="noti-uwp"> </div>
        <div>
            <h3>${uwp_var.text.List_of_unwanted_plugins}</h3>
            <p>${uwp_var.text.Enter_the_slugs_of_unwanted_plugins}</p>
            <p>${uwp_var.text.They_will_be_automatically_deleted}</p>
            <textarea name="plugin-list-uwp" id="plugin-list-uwp" rows="5" cols="50">${pluginlist}</textarea>
        </div>
        <div>
            <button class="button button-primary" id="saveButton">${uwp_var.text.save_changes}</button>
        </div>
        <br>
        <div>
        <p>${uwp_var.text.delete_now_hint}</p>
            <button class="button" id="deleteButton">${uwp_var.text.delete_unwanted_plugins}</button>
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
    const plugin_list = document.getElementById('plugin-list-uwp').value;
    noti_box = (m, clss) => {
        let noti = document.getElementById('noti-uwp')
        // noti.className = clss;
        noti.innerHTML = '<div class="notice notice-' + clss + ' ' + '" data-slug="unwanted-changer"><p>' + m + '</p></div>';
        el.innerHTML = d;
    }
    let el = document.getElementById(`${state}Button`);
    const d = el.innerHTML;
    el.blur();
    if(state == 'save'){
        el.innerHTML=uwp_var.text.saving;
    }else if(state == 'delete'){
        el.innerHTML=uwp_var.text.deleting;
    }

    data = {};
    let msg = 'not working';
    let clss = "not-valid-";
    jQuery(function ($) {
    
        data = {
            action: "handler_uwp",
            state: state,
            plugin_list: plugin_list,
            nonce: uwp_var.nonce
            };
            
        $.post(uwp_var.ajaxurl, data, function (res) {
            if(res.data.success ==true){
                msg = res.data.m;
                // clss= "valid";
                clss= "success";
                noti_box(msg ,clss);
            }else{
                msg = res.data.m;
                // clss = "not-valid";
                clss = "error";
                noti_box(msg ,clss);
            }  
        })
        return true;
    });
}
