let pluginlist_uncl = [];
document.addEventListener("DOMContentLoaded", function() {
    
    const pluginListuncl = uncl_var.plugin_list;
    
  /*   for (let key in pluginListuncl) {
        if (Object.prototype.hasOwnProperty.call(pluginListuncl, key)) {
            if (typeof pluginListuncl[key] === 'string') {
                pluginlist_uncl === null ? pluginlist_uncl = pluginListuncl[key].replace(/,/g, '\n') : pluginlist_uncl += '\n'+ pluginListuncl[key].replace(/,/g, '\n')
            }
        }
    } */
    
    const delete_ok= uncl_var.delete_ok == 'true' || uncl_var.delete_ok == 1 ? 1 : 0
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
                        <!-- textarea name="plugin-list-uncl" id="plugin-list-uncl" rows="5" cols="50">${pluginlist_uncl}</textarea -->
                        <!-- start new code -->
                        <div class="uncl section-dropdown">
                                <div class="uncl selected-list row col-12 my-3 mx-2" id="selectedList-uncl"></div>
                                <div class="uncl  dropdown col-8">                                
                                    <input type="text" id="dropdownInput-uncl" placeholder="Search...">
                                    <div class="uncl dropdown-content" id="dropdownList-uncl"></div>                                    
                                </div>
                                <a class="button button-primary col-3"  role="button" id="searchPlng-uncl"> Search</a>
                        </div>
                        <!-- end  new code-->
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
            fun_handle_uncl('save');
        });
    
        // Event-Handler for delete now button
        document.getElementById('deleteButton').addEventListener('click', function() {
            fun_handle_uncl('delete');
        });        
    }, 100);
});

function fun_handle_uncl(state){
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
/* uncl_var.plugin_dropdown_list.forEach(plugin => {
    console.log(plugin.name,plugin.icons['1x'])
}); */

//End new code for parsing Plugins


// Start new code for dropdown
document.addEventListener('DOMContentLoaded', function() {
const availableItems = [
    { name: "Item 1", image: "https://via.placeholder.com/30", id: "710" },
    { name: "Item 2", image: "https://via.placeholder.com/30", id: "720" },
    { name: "Item 3", image: "https://via.placeholder.com/30", id: "730" },
    { name: "Item 4", image: "https://via.placeholder.com/30", id: "740" }
];

const selectedItems = new Set();
const dropdownInput = document.getElementById('dropdownInput-uncl');
const dropdownList = document.getElementById('dropdownList-uncl');
const selectedList = document.getElementById('selectedList-uncl');
const searchPlng = document.getElementById('searchPlng-uncl');
let filteredItems = [];
let listReceivedFromWP_uncl = [];

// Filter and display the dropdown list


fun_addItemToSelectedList = (items) => {
    fun_addedItems =()=>{
        dropdownList.innerHTML = '';
        //const filteredItems = uncl_var.plugin_dropdown_list.filter(item => item.name.toLowerCase().includes(filter) && !selectedItems.has(item.name));
        if (filteredItems.length) {
            filteredItems.forEach(item => {
                const itemElement = document.createElement('div');
                itemElement.innerHTML = `<img src="${item.icons['1x'] ?? item.icons['default']}" alt="${item.name}"><span>${item.name}</span>`;
                itemElement.addEventListener('click', () => addItemToSelectedList(item));
                dropdownList.appendChild(itemElement);
            });
        } else {
            checkExternalSource(dropdownInput.value);
            return;
        }
        dropdownList.style.display = filteredItems.length ? 'block' : 'none';
    }//end of fun_addedItems
    if(items.length==0){
        dropdownList.innerHTML = `<div><span>Not Found any</span></div>`;
        return;
    }
   
    filteredItems =items;
    fun_addedItems();
    dropdownInput.addEventListener('input', function() {
        fun_addedItems();       
    });
}

//fun_searchPlng-uncl
searchPlng.addEventListener('click', function() {
    const name = dropdownInput.value;
     fun_fetch_plugin_list_uncl(name);
    //remove event click on dropdownInput
    
});

// Add item to selected list
function addItemToSelectedList(item) {
    if (!selectedItems.has(item.name)) {
        selectedItems.add(item.name);
        const itemRow = document.createElement('div');
        itemRow.className = 'item-row';
        itemRow.innerHTML = `
            <div class=" ${item.slug}">              
                    <img src="${item.icons['1x'] ?? item.icons['default']}" alt="${item.name}" width="30">
                    <span>${item.name}</span>
                    <span class="remove-button removeItemBtnFromListUncl" data-slug='${item.slug}' >&#120299;</span>
            </div>
        `;
        itemRow.querySelector('.remove-button').addEventListener('click', () => removeItemFromSelectedList(item.name, itemRow));
        selectedList.appendChild(itemRow);
        dropdownInput.value = '';
        dropdownList.innerHTML = '';
        dropdownList.style.display = 'none';
        fun_addPluginToList_uncl(item);
    }
}

// Remove item from selected list
function removeItemFromSelectedList(name, element) {
    selectedItems.delete(name);
    element.remove();
}

// Check external source if item is not found
function checkExternalSource(query) {
    // Simulate an API request
    setTimeout(() => {
        const externalItem = { name: query, image: "https://via.placeholder.com/30", id: "750" };
        const itemElement = document.createElement('div');
        itemElement.innerHTML = `<span>Not Found any</span>`;
        dropdownList.appendChild(itemElement);
        dropdownList.style.display = 'block';
    }, 500); // Simulate network delay
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
    console.log(data_filtered);
    fun_addItemToSelectedList(data_filtered);
});



//fetch the plugin list from the server https://api.wordpress.org/plugins/info/1.2/?action=query_plugins&request[search]=jetpack
async function fun_fetch_plugin_list_uncl(name ){
   // const response = await fetch(`https://api.wordpress.org/plugins/info/1.2/?action=query_plugins&request[search]=${name}`);
   fun_state_btn_searchPlnguncl(1);
   f_u=(lang , page , search)=>{
    return `https://api.wordpress.org/plugins/info/1.2/?action=query_plugins&request[search]=${search}&request[per_page]=100&request[page]=${page}&request[locale]=${lang}`;
    }

    f_progressbar= (total_page, page)=>{
        if(!document.getElementById('progressbar-uncl')){
            dropdownList.innerHTML = `<div class="progress px-0 my-4 mx-1">
                <div class="progress-bar" id="progressbar-uncl" role="progressbar" style="width:0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
            </div>`;
            dropdownList.style.display = 'block';
        }else{
            console.log(page/total_page);
            let r = (page/total_page);
            r = Math.floor(r*100);
            document.getElementById('progressbar-uncl').style.width = `${(r)}%`;
            document.getElementById('progressbar-uncl').innerHTML = `${(r)}%` ;
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
        fun_addItemToSelectedList(data_filtered);
    } catch (error) {
        console.error('Error fetching plugin list:', error);
    }
/*     const response = await fetch(url);
    const data = await response.json();
    let total_page = data.info.pages;
    page+=1;
     console.log(data);
   let data_filtered = data.plugins.filter(plugin => plugin.name.toLowerCase().includes(name.toLowerCase())).map(plugin => {
        return { name: plugin.name, icons: plugin.icons , slug:plugin.slug }
    }); */
    fun_state_btn_searchPlnguncl(0);
    return data_filtered;

}


// End new code for dropdown


/* functions of handling list of plugins */
fun_addPluginToList_uncl = (item) => {
    //pluginlist_uncl
    pluginlist_uncl.push([item.name, item.slug, item.icons['1x'] ?? item.icons['default']]);
}

/* End functions of handling list of plugins */

fun_state_btn_searchPlnguncl = (state) => {
    if(state==1){
        searchPlng.classList.add('disabled');
        searchPlng.disabled = true;
        searchPlng.innerHTML = 'loading...';
    }else{
        searchPlng.classList.remove('disabled');
        searchPlng.disabled = false;
        searchPlng.innerHTML = 'Search';
    }
  
}

});//end of dom content loaded
