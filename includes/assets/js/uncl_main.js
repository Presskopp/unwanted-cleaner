let pluginlist_uncl = [];
let themelist_uncl = [];

addItemUiselected=(item)=>{ 
    const icon = item.hasOwnProperty('icon') ? item.icon : item.icons['1x'] ?? item.icons['default'];

    return ` <div class="plugin-card-top  ${item.slug}">
                <div class="name column-name">
                    <h3>
                    <div class="action-links">
                        <div class="plugin-action-buttons">
                            <span class="btn btn-secondary btn-sm remove-button removeItemBtnFromListUncl" data-slug='${item.slug}' >&#120299;</span>
                        </div>
                    </div>
                    <p class="thickbox open-plugin-details-modal">
						${item.name}<img src="${icon}" class="plugin-icon" alt="">
					</p>            
                    </h3>
                </div>
            </div>`;
    }
    
document.addEventListener("DOMContentLoaded", function() {

    const delete_ok = (uncl_var.delete_ok === 'true' || uncl_var.delete_ok === 1) ? 1 : 0;
    const delete_ok_phrase_plugin = delete_ok ? replace_phrase_uncl(uncl_var.text.Plugins_will_be_automatically_deleted,'plugins') : replace_phrase_uncl(uncl_var.text.Plugins_can_be_manually_deleted ,'plugins')
    console.log(`delete_ok_phrase_plugin: ${delete_ok_phrase_plugin}`);
    const ui_page = `
    <style>
        #wpwrap {
            background-color: #f0f0f1;
        }

        .uncl-header {
            background-color: #fafafa;
        }

        .uncl-header-image {
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

        .uncl-header, .wrap, .uncl-footer {
            max-width: 67rem;
            display: block;
            margin: 15px auto 0;
        }

        ul#myTab {
            background: transparent;
        }
    </style>

    <div id="noti-uncl"> </div>
     <div class="uncl-header">
        <img class="uncl-header-image" src="${uncl_var.images}/banner-1544x500.png">
    </div>
    <div class="wrap">
        <!-- h1>${uncl_var.text.Unwanted_Cleaner_Settings}</h1 -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab1-tab" data-bs-toggle="tab" data-bs-target="#tab1" type="button" role="tab" aria-controls="tab1" aria-selected="true">${uncl_var.text.plugins}</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab2-tab" data-bs-toggle="tab" data-bs-target="#tab2" type="button" role="tab" aria-controls="tab2" aria-selected="false">${uncl_var.text.themes}</button>
            </li>

            <!-- for future use: li class="nav-item" role="presentation">
                <button class="nav-link" id="tab3-tab" data-bs-toggle="tab" data-bs-target="#tab3" type="button" role="tab" aria-controls="tab3" aria-selected="false">${uncl_var.text.files}</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab4-tab" data-bs-toggle="tab" data-bs-target="#tab4" type="button" role="tab" aria-controls="tab3" aria-selected="false">${uncl_var.text.database}</button>
            </li -->

        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="tab1" role="tabpanel" aria-labelledby="tab1-tab">
                <div class="uncl-panel">
                    <div>
                        <div class="uncl section-dropdown">
                            <div class="uncl dropdown col-8">                                
                                <input type="text" id="dropdownInput-uncl" >
                                <div class="uncl dropdown-content" id="dropdownList-uncl"></div>                                    
                            </div>
                            <a class="button button-primary" role="button" id="searchPlng-uncl">${uncl_var.text.search}</a>
                            <br><br>
                            <p id="automatic_hint">${delete_ok_phrase_plugin}</p>
                        </div>
                        <!-- div class="uncl selected-list my-3 mx-2" id="selectedList-uncl"></div -->
                        <div class="uncl selected-list" id="selectedList-uncl"></div>
                    </div>
                    <div id="delete_checkbox_section">
                        <input type="checkbox" id="delete_ok" name="delete_ok" value="0" ${delete_ok ? 'checked' : ''}>
                        <label for="delete_ok">${replace_phrase_uncl(uncl_var.text.Automatic_deletion_confirmation,'plugins')}</label><br><br>
                    </div>
                    <div>
                       <button class="button button-primary d-none" id="saveButton">${uncl_var.text.save_changes}</button>
                    </div>
                    <br>
                    <div>
                    <p>${replace_phrase_uncl(uncl_var.text.delete_now_hint ,'plugins')}</p>
                        <button class="button" id="deleteButton">${replace_phrase_uncl(uncl_var.text.delete_unwanted_plugins , 'plugins')}</button>
                    </div>
                </div>
            </div>
            <!-- Themes Section -->
            <div class="tab-pane fade" id="tab2" role="tabpanel" aria-labelledby="tab2-tab">
                <div>
                    <div class="uncl section-dropdown">
                        <div class="uncl dropdown col-8">                                
                            <input type="text" id="themes-dropdownInput-uncl" >
                            <div class="uncl dropdown-content" id="themes-dropdownList-uncl"></div>                                    
                        </div>
                        <a class="button button-primary" role="button" id="themes-searchPlng-uncl">${uncl_var.text.search}</a>
                        <br><br>
                        <p id="automatic_hint">${delete_ok ? replace_phrase_uncl(uncl_var.text.Plugins_will_be_automatically_deleted,'themes') : replace_phrase_uncl(uncl_var.text.Plugins_can_be_manually_deleted ,'themes')}</p>
                    </div>
                    <!-- div class="uncl selected-list my-3 mx-2" id="selectedList-uncl"></div -->
                    <div class="uncl selected-list" id="themes-selectedList-uncl"></div>
                </div>
                <div id="themes_delete_checkbox_section">
                    <input type="checkbox" id="themes_delete_ok" name="themes_delete_ok" value="0" ${delete_ok ? 'checked' : ''}>
                    <label for="themes_delete_ok">${replace_phrase_uncl(uncl_var.text.Automatic_deletion_confirmation,'themes')}</label><br><br>
                </div>                    
                <br>
                <div>
                <p>${replace_phrase_uncl(uncl_var.text.delete_now_hint ,'themes')}</p>
                    <button class="button" id="themes-deleteButton">${replace_phrase_uncl(uncl_var.text.delete_unwanted_plugins , 'themes')}</button>
                </div>
            </div>
            <!-- Themes Section -->
        </div>
    </div>
    <br>
    <div class="uncl-footer">Thank you for using <a href="https://presskopp.com/en/unwanted-cleaner">Unwanted Cleaner</a>. If you like it, you may consider <a href="https://presskopp.com/en/unwanted-cleaner/#main-footer"> buying me a coffee</a></div>

    <!-- Vertically centered modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark py-1">
                <h5 class="modal-title" id="errorModalLabel">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="30px" height="30px">
                    <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                    </svg>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            </div>
            <!--
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            -->
        </div>
    </div>
    </div>
    `;
    document.getElementById('main_unwanted_cleaner').innerHTML = ui_page;

    setTimeout(() => {
        // Event-Handler for save button
        document.getElementById('saveButton').addEventListener('click', function() {
            fun_handle_uncl('save');
        });
    
        // Event-Handler for delete now button
        document.getElementById('deleteButton').addEventListener('click', function() {
            fun_handle_uncl('delete_plugins');
        });    
        
        document.getElementById('themes-deleteButton').addEventListener('click', function() {
            fun_handle_uncl('delete_themes');
        });
    }, 200);
});

function fun_handle_uncl(state){
    //const plugin_list = document.getElementById('plugin-list-uncl').value;
    //delete duplicate
    const pluginlist_uncl_ = pluginlist_uncl.filter((v,i,a)=>a.findIndex(t=>(t.slug === v.slug))===i);
    const themelist_uncl_ = themelist_uncl.filter((v,i,a)=>a.findIndex(t=>(t.slug === v.slug))===i);
    console.log('save/delete plugin:', pluginlist_uncl_);
    console.log('save/delete theme:', themelist_uncl_);
    const plugin_list = JSON.stringify(pluginlist_uncl_);
    const theme_list = JSON.stringify(themelist_uncl_);
    //console.log(plugin_list);

    const checkbox_delete = document.getElementById('delete_ok').checked;
    let el = document.getElementById(`deleteButton`);

    const d = el.innerHTML;
    el.blur();
    if(state == 'delete'){
      
       const deleting = replace_phrase_uncl(uncl_var.text.deleting, 'plugins');
        el.innerHTML=deleting;
    }

    data = {};
    let msg = 'not working';
    let clss = "not-valid-";
    jQuery(function ($) {
    
        data = {
            action: "uncl_handler",
            state: state,
            plugin_list: plugin_list,
            theme_list: theme_list,
            delete_ok: checkbox_delete,
            nonce: uncl_var.nonce
            };
            
        $.post(uncl_var.ajaxurl, data, function (res) {
            if(res.data.success == true){
                msg = res.data.m;
                // clss= "valid";
                clss= "success";
               if(state!='save'){ 
                noti_box_uncl(msg ,clss);
                el.innerHTML = d;
                }
            } else {
                msg = res.data.m;
                // clss = "not-valid";
                clss = "error";
                noti_box_uncl(msg ,clss);
                el.innerHTML = d;
            }  
        })
        return true;
    });
}

//Start new code for parsing Plugins
/* uncl_var.plugin_dropdown_list.forEach(plugin => {
    console.log(plugin.name,plugin.icons['1x'])
}); */

//End new code for parsing Plugins

// Start new code for dropdown

document.addEventListener('DOMContentLoaded', function() {

    const selectedItems = new Set();
    const dropdownInput = document.getElementById('dropdownInput-uncl');
    const dropdownList = document.getElementById('dropdownList-uncl');
    const selectedList = document.getElementById('selectedList-uncl');
    const searchPlng = document.getElementById('searchPlng-uncl');
    const selectedItems_themes = new Set();
    const dropdownInput_themes = document.getElementById('themes-dropdownInput-uncl');
    const dropdownList_themes = document.getElementById('themes-dropdownList-uncl');
    const selectedList_themes = document.getElementById('themes-selectedList-uncl');
    const searchPlng_themes = document.getElementById('themes-searchPlng-uncl');
    let filteredItems_plugin = [];
    let filteredItems_themes = [];
    let listReceivedFromWP_uncl = [];

    // Filter and display the dropdown list
    fun_addItemToSelectedList = (items ,context) => {
        console.log('DEBUG: ' + items , context);
        fun_addedItems =()=>{
            dropdownList.innerHTML = '';
         
            if (filteredItems_plugin.length) {
                filteredItems_plugin.forEach(item => {
                    const itemElement = document.createElement('div');
                    itemElement.innerHTML = `<img src="${item.icons['1x'] ?? item.icons['default']}" alt="${item.name}"><span>${item.name}</span>`;
                    itemElement.addEventListener('click', () => addItemToSelectedList(item , 'plugins'));
                    dropdownList.appendChild(itemElement);
                });
            } 
            dropdownList.style.display = filteredItems_plugin.length ? 'block' : 'none';
        }//end of fun_addedItems

        fun_addedItems_themes = () => {
            dropdownList_themes.innerHTML = '';
            
            if (filteredItems_themes.length) {
                filteredItems_themes.forEach(item => {
                    const itemElement = document.createElement('div');
                    itemElement.innerHTML = `<img src="${item.icons['1x'] ?? item.icons['default']}" alt="${item.name}"><span>${item.name}</span>`;
                    itemElement.addEventListener('click', () => addItemToSelectedList(item, 'themes'));
                    dropdownList_themes.appendChild(itemElement);
                });
            }
            dropdownList_themes.style.display = filteredItems_themes.length ? 'block' : 'none';
        }//end of fun_addedItems_themes
     
    
     
        
        if(context=='plugins'){
            filteredItems_plugin =items;
            if(items.length==0){
     
                const no_plugin_found = replace_phrase_uncl(uncl_var.text.no_plugin_found, 'plugins');
                dropdownList.innerHTML = `<div><span>${no_plugin_found}</span></div>`;
                return;
            }
            fun_addedItems();
            dropdownInput.addEventListener('input', function() {
                fun_addedItems();       
            });
        } else {
            filteredItems_themes=items;
            if(items.length==0){
                const no_theme_found = replace_phrase_uncl(uncl_var.text.no_plugin_found, 'themes');
                dropdownList_themes.innerHTML = `<div><span>${no_theme_found}</span></div>`;
                return;
            }
            fun_addedItems_themes();
            dropdownInput_themes.addEventListener('input', function() {
                fun_addedItems_themes();
            });
        }
    }

    //fun_searchPlng-uncl
    searchPlng.addEventListener('click', function() {
        const name = dropdownInput.value;
        if (name.length < 2) {
            noti_box_uncl(uncl_var.text.please_enter_atleast_2chrs, 'error');
            return false;
        }
        fun_fetch_plugin_list_uncl(name);
        //remove event click on dropdownInput
    });

    //searchPlng_themes
    searchPlng_themes.addEventListener('click', function() {
        const name = dropdownInput_themes.value;
        if (name.length < 2) {
            noti_box_uncl(uncl_var.text.please_enter_atleast_2chrs, 'error');
            return false;
        }
        fun_fetch_theme_list_uncl(name);
        //remove event click on dropdownInput
    });

    // Add item to selected list
    function addItemToSelectedList(item, context) {
        console.log('DEBUG: ' + item.name);
        // Plugin already on the list?
      

        if (!item.name) {
            console.error("Plugin name missing.");
            return;
        }

        if (item.name === "Unwanted Cleaner") { 
            try {
                throw new Error(uncl_var.text.no_select_uc);
            } catch (error) {
                showErrorModal(error.message);
            }
            return;
        };
        if(context=='plugins'){
            if (selectedItems.has(item.name)) {
                try {
                    throw new Error("The selected item has already been added!");
                } catch (error) {
                    showErrorModal(error.message);
                }
                return;
            }
        // Plugin not on the list
        selectedItems.add(item.name);
        const itemRow = document.createElement('div');
        itemRow.className = 'plugin-card';
        itemRow.innerHTML = addItemUiselected(item, context);
        itemRow.querySelector('.remove-button').addEventListener('click', () => removeItemFromSelectedList(item.name, item.slug ,itemRow ,context));
        selectedList.appendChild(itemRow);
        dropdownInput.value = '';
        dropdownList.innerHTML = '';
        dropdownList.style.display = 'none';
        fun_addPluginToList_uncl(item);
        }else{
            // Theme not on the list
            if (selectedItems_themes.has(item.name)) {
                try {
                    throw new Error("The selected item has already been added!");
                } catch (error) {
                    showErrorModal(error.message);
                }
                return;
            }
            selectedItems_themes.add(item.name);
            const itemRow = document.createElement('div');
            itemRow.className = 'plugin-card';
            itemRow.innerHTML = addItemUiselected(item, context);
            itemRow.querySelector('.remove-button').addEventListener('click', () => removeItemFromSelectedList(item.name, item.slug ,itemRow ,context));
            selectedList_themes.appendChild(itemRow);
            dropdownInput_themes.value = '';
            dropdownList_themes.innerHTML = '';
            dropdownList_themes.style.display = 'none';
            fun_addThemeToList_uncl(item);
        }
    }

    // Remove item from selected list
    function removeItemFromSelectedList(name, slug , element ,context) {
        if(context=='plugins') {
            selectedItems.delete(name);
            element.remove();
            pluginlist_uncl = pluginlist_uncl.filter(plugin => plugin.slug != slug);
            console.log(pluginlist_uncl);        
        }else{
            selectedItems_themes.delete(name);
            element.remove();
            themelist_uncl = themelist_uncl.filter(theme => theme.slug != slug);
            console.log(themelist_uncl);
        }


        setTimeout(() => {
            fun_handle_uncl('save');
        }, 100);
    }

    // Hide the dropdown list when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.dropdown')) {
            dropdownList.style.display = 'none';
        }
    });

    dropdownInput.addEventListener('click', function(event) {
        event.stopPropagation();
        dropdownList.style.display = 'block';
    });

    dropdownInput.addEventListener('input', function() {
        if(listReceivedFromWP_uncl.length==0)return;
        const value = dropdownInput.value;    
        const data_filtered = listReceivedFromWP_uncl.filter(plugin => plugin.name.toLowerCase().includes(value.toLowerCase())).map(plugin => {
            return { name: plugin.name, icons: plugin.icons , slug:plugin.slug }
        });
        // list of found plugins
        //console.log(data_filtered);
        fun_addItemToSelectedList(data_filtered ,'plugins');
    });

    document.getElementById('delete_ok').addEventListener('click', function() {
        // 2DO: Save option !?
        let automaticHint = document.getElementById('automatic_hint');
        fun_handle_uncl('auto');
        if (this.checked) {
            automaticHint.innerHTML = `${uncl_var.text.Plugins_will_be_automatically_deleted}`;
        }
        else {
            automaticHint.innerHTML = `${uncl_var.text.Plugins_can_be_manually_deleted}`;
        }
    });

    // fetch the plugin list from the server https://api.wordpress.org/plugins/info/1.2/?action=query_plugins&request[search]=jetpack
    async function fun_fetch_plugin_list_uncl(name ){
        
     //let progressBar = document.getElementById('progressbar-uncl');

        // const response = await fetch(`https://api.wordpress.org/plugins/info/1.2/?action=query_plugins&request[search]=${name}`);
        fun_state_btn_searchPlnguncl(1);
        f_u=(lang , page , search)=>{
            return `https://api.wordpress.org/plugins/info/1.2/?action=query_plugins&request[search]=${search}&request[per_page]=100&request[page]=${page}&request[locale]=${lang}`;
        }

        f_progressbar= (total_page, page)=>{
            let progressBar = document.getElementById('progressbar-uncl')
            if (!progressBar){
                dropdownList.innerHTML = `<div class="progress px-0 my-4 mx-1">
                    <div class="progress-bar" id="progressbar-uncl" role="progressbar" style="width:0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>`;
                dropdownList.style.display = 'block';
            } else {
                console.log(page/total_page);
                let r = Math.floor((page / total_page) * 100);
                progressBar.style.width = `${r}%`;
                progressBar.innerHTML = `${r}%`;
            }
        }

        let page = 1;
        let data_filtered = [];
        let total_page = 0;

        try {
            do {
                const url = f_u(uncl_var.user_lang, page, name);
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                const r = data.plugins.filter(plugin => plugin.name.toLowerCase().includes(name.toLowerCase())).map(plugin => {
                    return { name: plugin.name, icons: plugin.icons, slug: plugin.slug }
                });
                data_filtered = data_filtered.concat(r);
                console.log(data_filtered);
                if (total_page == 0 && data.info && data.info.pages) {
                    console.log('total_page:', data.info.pages );
                    //data.info.results
                    total_page = data.info.pages;                                
                }
                f_progressbar(total_page, page);
                page += 1;
            } while (page <= total_page);
            listReceivedFromWP_uncl = data_filtered;
            fun_addItemToSelectedList(data_filtered ,'plugins');
            //delete the progressBar tag



        } catch (error) {
            console.error('Error fetching plugin list:', error);
            showErrorModal(uncl_var.text.error_load_fetch);
            let progressBar =document.getElementById('progressbar-uncl');
            if(progressBar) progressBar.style.display = 'none';
        }

        fun_state_btn_searchPlnguncl(0);
        return data_filtered;

    }
    // End new code for dropdown
    // fetch the plugin list from the server https://api.wordpress.org/plugins/info/1.2/?action=query_plugins&request[search]=jetpack
    async function fun_fetch_theme_list_uncl(name ){
        
        fun_state_btn_searchPlnguncl(1);
    
        // Modify this function to use the themes API instead of the plugins API
        f_u = (lang , page , search) => {
            return `https://api.wordpress.org/themes/info/1.2/?action=query_themes&request[search]=${search}&request[per_page]=100&request[page]=${page}&request[locale]=${lang}`;
        }
    
        f_progressbar = (total_page, page) => {
            let progressBar = document.getElementById('themes-progressbar-uncl')
            if (!progressBar){
                dropdownList_themes.innerHTML = `<div class="progress px-0 my-4 mx-1">
                    <div class="progress-bar" id="themes-progressbar-uncl" role="progressbar" style="width:0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>`;
                dropdownList_themes.style.display = 'block';
            } else {
                console.log(page/total_page);
                let r = Math.floor((page / total_page) * 100);
                progressBar.style.width = `${r}%`;
                progressBar.innerHTML = `${r}%`;
            }
        }
    
        let page = 1;
        let data_filtered = [];
        let total_page = 0;
    
        try {
            do {
                const url = f_u(uncl_var.user_lang, page, name); // Call the themes API URL
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                
                // Filter and map themes data
                const r = data.themes.filter(theme => theme.name.toLowerCase().includes(name.toLowerCase())).map(theme => {
                    return { name: theme.name, icons:{'1x':theme.screenshot_url}, slug: theme.slug }
                });
                data_filtered = data_filtered.concat(r);
                console.log(data_filtered);
                if (total_page == 0 && data.info && data.info.pages) {
                    console.log('total_page:', data.info.pages );
                    total_page = data.info.pages;                                
                }
                f_progressbar(total_page, page);
                page += 1;
            } while (page <= total_page);

            if(total_page==0 || total_page==1){
                console.log('total_page:', total_page );
                f_progressbar(total_page, 1);
            }
            
            listReceivedFromWP_uncl = data_filtered;
            console.log('listReceivedFromWP_uncl:', listReceivedFromWP_uncl);
            fun_addItemToSelectedList(data_filtered,'themes');
    
        } catch (error) {
            console.error('Error fetching theme list:', error);
            showErrorModal(uncl_var.text.error_load_fetch);
            let progressBar = document.getElementById('themes-progressbar-uncl');
            if(progressBar) progressBar.style.display = 'none';
        }
    
        fun_state_btn_searchPlnguncl(0);
        return data_filtered;
    }
    
    // End new code for dropdown

    /* functions of handling list of plugins */
    fun_addPluginToList_uncl = (item) => {
        //pluginlist_uncl
        ///item.name, item.slug, item.icons['1x'] ?? item.icons['default']
        const icon =item.hasOwnProperty('icon') ? item.icon : item.icons['1x'] ?? item.icons['default'];
        pluginlist_uncl.push({ name: item.name, slug: item.slug, icon: icon });
        console.log(pluginlist_uncl);

        setTimeout(() => {
            fun_handle_uncl('save');
        }, 100);
    }
    fun_addThemeToList_uncl = (item) => {
        //pluginlist_uncl
        ///item.name, item.slug, item.icons['1x'] ?? item.icons['default']
        const icon =item.hasOwnProperty('icon') ? item.icon : item.icons['1x'] ?? item.icons['default'];
        themelist_uncl.push({ name: item.name, slug: item.slug, icon: icon });
        console.log(themelist_uncl);

        setTimeout(() => {
            fun_handle_uncl('save');
        }, 100);
    }

    /* End functions of handling list of plugins */

    fun_state_btn_searchPlnguncl = (state) => {
        searchPlng.classList.toggle('disabled', state == 1);
        searchPlng.disabled = state == 1;
        searchPlng.innerHTML = state == 1 ? uncl_var.text.loading : uncl_var.text.search;
    };

    if(uncl_var.plugin_list) {
        // let tem= uncl_var.plugin_list.replace(/[\\]/g, '');
        pluginlist_uncl = JSON.parse(uncl_var.plugin_list);
    }

    if(uncl_var.theme_list) {
        // let tem= uncl_var.plugin_list.replace(/[\\]/g, '');
        themelist_uncl = JSON.parse(uncl_var.theme_list);
    }
    
    //let pluginListUi = '<!-- unc -->';    2DO what for?
    pluginlist_uncl.forEach(plugin => {
        addItemToSelectedList(plugin , 'plugins');
    });

    themelist_uncl.forEach(theme => {
        addItemToSelectedList(theme , 'themes');
    });

});//end of dom content loaded

// workaround for plugin list columns 
document.addEventListener("DOMContentLoaded", function() {
    function applyStylesToPluginCards() {
        const pluginCards = document.querySelectorAll('.plugin-card');

        if (window.innerWidth < 1600) {
            pluginCards.forEach(function(card) {
                card.style.maxWidth = '';
            });
        }
        else if (window.matchMedia('(min-width: 1600px) and (max-width: 2299px)').matches) {
            pluginCards.forEach(function(card) {
                card.style.maxWidth = '32.2%';
            });
        }
    }

    applyStylesToPluginCards();

    window.addEventListener('resize', applyStylesToPluginCards);

    const observer = new MutationObserver(function(mutationsList) {
        mutationsList.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(function(node) {
                    if (node.classList && node.classList.contains('plugin-card')) {
                        applyStylesToPluginCards();
                    }
                });
            }
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});

function showErrorModal(errorMessage) {
    if (errorMessage) {
        let errorModal =  document.querySelector('#errorModal .modal-body');
        errorModal.innerHTML = `<h6 class="my-3">${errorMessage}</h6>`;
    }
    let myModal = new bootstrap.Modal(document.getElementById('errorModal'), {
        keyboard: true
    });
    myModal.show();
}

function replace_phrase_uncl(phrase, context) {
    const s = uncl_var.text[context];
   
    console.log(`[${phrase}]`,`[${context}]`,phrase.replace('%s', s), s);
    const r = phrase.replace('%s', s);
    console.log(r);
    return r;
}


noti_box_uncl = (m, clss) => {
    let noti = document.getElementById('noti-uncl')
    // noti.className = clss;
    noti.innerHTML = '<div class="notice notice-' + clss + ' ' + '" data-slug="unwanted-changer"><p>' + m + '</p></div>';
  
}
