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
    
    const delete_ok= uncl_var.delete_ok == 'true' || uncl_var.delete_ok == 1 ? 1 : 0
    const ui_page = `
    <style>
        #wpwrap {
            background-color: #f0f0f1;
        }

        .uwcl-header {
            background-color: #fafafa;
        }

        .uwcl-header-image {
            width: 50%;
            height: auto; /* to keep aspect ratio */
            display: block;
            margin: 0 auto;
        }

        .tab-content {
            background-color: #fff;
            padding: 16px;
        }

        .nav-link:not(.active) {
            background-color: #d9d9d9 !important;
        }

        .nav-tabs .nav-link {
            color: #000;
            margin-bottom: -7px;
        }

        .uwcl-header, .wrap, .uncl-footer {
            max-width: 67rem;
            display: block;
            margin: 15px auto 0;
        }

        ul#myTab {
            background: transparent;
        }
    </style>

    <div id="noti-uncl"> </div>
     <div class="uwcl-header">
        <img class="uwcl-header-image" src="/wp-content/plugins/unwanted-cleaner/includes/assets/img/banner-1544x500.png">
    </div>
    <div class="wrap">
        <!-- h1>${uncl_var.text.Unwanted_Cleaner_Settings}</h1 -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab1-tab" data-bs-toggle="tab" data-bs-target="#tab1" type="button" role="tab" aria-controls="tab1" aria-selected="true">Plugins</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab2-tab" data-bs-toggle="tab" data-bs-target="#tab2" type="button" role="tab" aria-controls="tab2" aria-selected="false">Themes</button>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="tab1" role="tabpanel" aria-labelledby="tab1-tab">
                <div class="uncl-panel">
                    <div>
                        <p>${uncl_var.text.Enter_the_slugs_of_unwanted_plugins}<br>
                        ${uncl_var.text.They_will_be_automatically_deleted}</p>
                        <textarea name="plugin-list-uncl" id="plugin-list-uncl" rows="5" cols="50">${pluginlist}</textarea>
                    </div>
                    <div>
                        <input type="checkbox" id="delete_ok" name="delete_ok" value="0" ${delete_ok ? 'checked' : ''}>
                        <label for="delete_ok">${uncl_var.text.Automatic_deletion_confirmation}</label><br><br>
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
            </div>
            <div class="tab-pane fade" id="tab2" role="tabpanel" aria-labelledby="tab2-tab">
                <h1>Here come the themes!</h1>
            </div>
        </div>
    </div>
    <br>
    <div class="uncl-footer">Thank you for using <a href="https://presskopp.com/en/unwanted-cleaner">Unwanted Cleaner</a>. If you like it, you may consider <a href="https://presskopp.com/en/unwanted-cleaner/#main-footer"> buying me a coffee</a></div>
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


//Start new code for parsing Plugins
uncl_var.plugin_dropdown_list.forEach(plugin => {
    console.log(plugin.name,plugin.icons['1x'])
});

//End new code for parsing Plugins
