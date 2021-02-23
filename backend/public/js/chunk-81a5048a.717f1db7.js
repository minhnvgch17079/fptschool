(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-81a5048a"],{2471:function(t,e,a){},"5fad":function(t,e,a){"use strict";a.r(e);var s=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"data-list-container",attrs:{id:"data-list-list-view"}},[a("data-view-sidebar",{attrs:{isSidebarActive:t.addNewDataSidebar,data:t.sidebarData},on:{closeSidebar:t.toggleDataSidebar}}),a("vs-table",{ref:"table",attrs:{multiple:"",pagination:"","max-items":t.itemsPerPage,search:"",data:t.products},scopedSlots:t._u([{key:"default",fn:function(e){var s=e.data;return[a("tbody",t._l(s,(function(e,s){return a("vs-tr",{key:s,attrs:{data:e}},[a("vs-td",[a("p",{staticClass:"product-name font-medium truncate"},[t._v(t._s(e.name))])]),a("vs-td",[a("p",{staticClass:"product-category"},[t._v(t._s(t._f("title")(e.category)))])]),a("vs-td",[a("vs-progress",{staticClass:"shadow-md",attrs:{percent:Number(e.popularity),color:t.getPopularityColor(Number(e.popularity))}})],1),a("vs-td",[a("vs-chip",{staticClass:"product-order-status",attrs:{color:t.getOrderStatusColor(e.order_status)}},[t._v(t._s(t._f("title")(e.order_status)))])],1),a("vs-td",[a("p",{staticClass:"product-price"},[t._v("$"+t._s(e.price))])]),a("vs-td",{staticClass:"whitespace-no-wrap"},[a("feather-icon",{attrs:{icon:"EditIcon",svgClasses:"w-5 h-5 hover:text-primary stroke-current"},on:{click:function(a){return a.stopPropagation(),t.editData(e)}}}),a("feather-icon",{staticClass:"ml-2",attrs:{icon:"TrashIcon",svgClasses:"w-5 h-5 hover:text-danger stroke-current"},on:{click:function(a){return a.stopPropagation(),t.deleteData(e.id)}}})],1)],1)})),1)]}}]),model:{value:t.selected,callback:function(e){t.selected=e},expression:"selected"}},[a("div",{staticClass:"flex flex-wrap-reverse items-center flex-grow justify-between",attrs:{slot:"header"},slot:"header"},[a("div",{staticClass:"flex flex-wrap-reverse items-center data-list-btn-container"},[a("vs-dropdown",{staticClass:"dd-actions cursor-pointer mr-4 mb-4",attrs:{"vs-trigger-click":""}},[a("div",{staticClass:"p-4 shadow-drop rounded-lg d-theme-dark-bg cursor-pointer flex items-center justify-center text-lg font-medium w-32 w-full"},[a("span",{staticClass:"mr-2"},[t._v("Actions")]),a("feather-icon",{attrs:{icon:"ChevronDownIcon",svgClasses:"h-4 w-4"}})],1),a("vs-dropdown-menu",[a("vs-dropdown-item",[a("span",{staticClass:"flex items-center"},[a("feather-icon",{staticClass:"mr-2",attrs:{icon:"TrashIcon",svgClasses:"h-4 w-4"}}),a("span",[t._v("Delete")])],1)]),a("vs-dropdown-item",[a("span",{staticClass:"flex items-center"},[a("feather-icon",{staticClass:"mr-2",attrs:{icon:"ArchiveIcon",svgClasses:"h-4 w-4"}}),a("span",[t._v("Archive")])],1)]),a("vs-dropdown-item",[a("span",{staticClass:"flex items-center"},[a("feather-icon",{staticClass:"mr-2",attrs:{icon:"FileIcon",svgClasses:"h-4 w-4"}}),a("span",[t._v("Print")])],1)]),a("vs-dropdown-item",[a("span",{staticClass:"flex items-center"},[a("feather-icon",{staticClass:"mr-2",attrs:{icon:"SaveIcon",svgClasses:"h-4 w-4"}}),a("span",[t._v("Another Action")])],1)])],1)],1),a("div",{staticClass:"btn-add-new p-3 mb-4 mr-4 rounded-lg cursor-pointer flex items-center justify-center text-lg font-medium text-base text-primary border border-solid border-primary",on:{click:t.addNewData}},[a("feather-icon",{attrs:{icon:"PlusIcon",svgClasses:"h-4 w-4"}}),a("span",{staticClass:"ml-2 text-base text-primary"},[t._v("Add New")])],1)],1),a("vs-dropdown",{staticClass:"cursor-pointer mb-4 mr-4 items-per-page-handler",attrs:{"vs-trigger-click":""}},[a("div",{staticClass:"p-4 border border-solid d-theme-border-grey-light rounded-full d-theme-dark-bg cursor-pointer flex items-center justify-between font-medium"},[a("span",{staticClass:"mr-2"},[t._v(t._s(t.currentPage*t.itemsPerPage-(t.itemsPerPage-1))+" - "+t._s(t.products.length-t.currentPage*t.itemsPerPage>0?t.currentPage*t.itemsPerPage:t.products.length)+" of "+t._s(t.queriedItems))]),a("feather-icon",{attrs:{icon:"ChevronDownIcon",svgClasses:"h-4 w-4"}})],1),a("vs-dropdown-menu",[a("vs-dropdown-item",{on:{click:function(e){t.itemsPerPage=4}}},[a("span",[t._v("4")])]),a("vs-dropdown-item",{on:{click:function(e){t.itemsPerPage=10}}},[a("span",[t._v("10")])]),a("vs-dropdown-item",{on:{click:function(e){t.itemsPerPage=15}}},[a("span",[t._v("15")])]),a("vs-dropdown-item",{on:{click:function(e){t.itemsPerPage=20}}},[a("span",[t._v("20")])])],1)],1)],1),a("template",{slot:"thead"},[a("vs-th",{attrs:{"sort-key":"name"}},[t._v("Name")]),a("vs-th",{attrs:{"sort-key":"category"}},[t._v("Category")]),a("vs-th",{attrs:{"sort-key":"popularity"}},[t._v("Popularity")]),a("vs-th",{attrs:{"sort-key":"order_status"}},[t._v("Order Status")]),a("vs-th",{attrs:{"sort-key":"price"}},[t._v("Price")]),a("vs-th",[t._v("Action")])],1)],2)],1)},r=[],i=a("6dd9"),n=a("ee31"),o={components:{DataViewSidebar:i["a"]},data:function(){return{selected:[],itemsPerPage:4,isMounted:!1,addNewDataSidebar:!1,sidebarData:{}}},computed:{currentPage:function(){return this.isMounted?this.$refs.table.currentx:0},products:function(){return this.$store.state.dataList.products},queriedItems:function(){return this.$refs.table?this.$refs.table.queriedResults.length:this.products.length}},methods:{addNewData:function(){this.sidebarData={},this.toggleDataSidebar(!0)},deleteData:function(t){this.$store.dispatch("dataList/removeItem",t).catch((function(t){console.error(t)}))},editData:function(t){this.sidebarData=t,this.toggleDataSidebar(!0)},getOrderStatusColor:function(t){return"on_hold"==t?"warning":"delivered"==t?"success":"canceled"==t?"danger":"primary"},getPopularityColor:function(t){return t>90?"success":t>70?"primary":t>=50?"warning":t<50?"danger":"primary"},toggleDataSidebar:function(){var t=arguments.length>0&&void 0!==arguments[0]&&arguments[0];this.addNewDataSidebar=t}},created:function(){n["a"].isRegistered||(this.$store.registerModule("dataList",n["a"]),n["a"].isRegistered=!0),this.$store.dispatch("dataList/fetchDataListItems")},mounted:function(){this.isMounted=!0}},c=o,d=(a("9153"),a("2877")),l=Object(d["a"])(c,s,r,!1,null,null,null);e["default"]=l.exports},"6dd9":function(t,e,a){"use strict";var s=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("vs-sidebar",{staticClass:"add-new-data-sidebar items-no-padding",attrs:{"click-not-close":"","position-right":"",parent:"body","default-index":"1",color:"primary",spacer:""},model:{value:t.isSidebarActiveLocal,callback:function(e){t.isSidebarActiveLocal=e},expression:"isSidebarActiveLocal"}},[a("div",{staticClass:"mt-6 flex items-center justify-between px-6"},[a("h4",[t._v(t._s(0===Object.entries(this.data).length?"ADD NEW":"UPDATE")+" ITEM")]),a("feather-icon",{staticClass:"cursor-pointer",attrs:{icon:"XIcon"},on:{click:function(e){e.stopPropagation(),t.isSidebarActiveLocal=!1}}})],1),a("vs-divider",{staticClass:"mb-0"}),a("VuePerfectScrollbar",{key:t.$vs.rtl,staticClass:"scroll-area--data-list-add-new",attrs:{settings:t.settings}},[a("div",{staticClass:"p-6"},[t.dataImg?[a("div",{staticClass:"img-container w-64 mx-auto flex items-center justify-center"},[a("img",{staticClass:"responsive",attrs:{src:t.dataImg,alt:"img"}})]),a("div",{staticClass:"modify-img flex justify-between mt-5"},[a("input",{ref:"updateImgInput",staticClass:"hidden",attrs:{type:"file",accept:"image/*"},on:{change:t.updateCurrImg}}),a("vs-button",{staticClass:"mr-4",attrs:{type:"flat"},on:{click:function(e){return t.$refs.updateImgInput.click()}}},[t._v("Update Image")]),a("vs-button",{attrs:{type:"flat",color:"#999"},on:{click:function(e){t.dataImg=null}}},[t._v("Remove Image")])],1)]:t._e(),a("vs-input",{directives:[{name:"validate",rawName:"v-validate",value:"required",expression:"'required'"}],staticClass:"mt-5 w-full",attrs:{label:"Name",name:"item-name"},model:{value:t.dataName,callback:function(e){t.dataName=e},expression:"dataName"}}),a("span",{directives:[{name:"show",rawName:"v-show",value:t.errors.has("item-name"),expression:"errors.has('item-name')"}],staticClass:"text-danger text-sm"},[t._v(t._s(t.errors.first("item-name")))]),a("vs-select",{directives:[{name:"validate",rawName:"v-validate",value:"required",expression:"'required'"}],staticClass:"mt-5 w-full",attrs:{label:"Category",name:"item-category"},model:{value:t.dataCategory,callback:function(e){t.dataCategory=e},expression:"dataCategory"}},t._l(t.category_choices,(function(t){return a("vs-select-item",{key:t.value,attrs:{value:t.value,text:t.text}})})),1),a("span",{directives:[{name:"show",rawName:"v-show",value:t.errors.has("item-category"),expression:"errors.has('item-category')"}],staticClass:"text-danger text-sm"},[t._v(t._s(t.errors.first("item-category")))]),a("vs-select",{staticClass:"mt-5 w-full",attrs:{label:"Order Status"},model:{value:t.dataOrder_status,callback:function(e){t.dataOrder_status=e},expression:"dataOrder_status"}},t._l(t.order_status_choices,(function(t){return a("vs-select-item",{key:t.value,attrs:{value:t.value,text:t.text}})})),1),a("vs-input",{directives:[{name:"validate",rawName:"v-validate",value:{required:!0,regex:/\d+(\.\d+)?$/},expression:"{ required: true, regex: /\\d+(\\.\\d+)?$/ }"}],staticClass:"mt-5 w-full",attrs:{"icon-pack":"feather",icon:"icon-dollar-sign",label:"Price",name:"item-price"},model:{value:t.dataPrice,callback:function(e){t.dataPrice=e},expression:"dataPrice"}}),a("span",{directives:[{name:"show",rawName:"v-show",value:t.errors.has("item-price"),expression:"errors.has('item-price')"}],staticClass:"text-danger text-sm"},[t._v(t._s(t.errors.first("item-price")))]),t.dataImg?t._e():a("div",{staticClass:"upload-img mt-5"},[a("input",{ref:"uploadImgInput",staticClass:"hidden",attrs:{type:"file",accept:"image/*"},on:{change:t.updateCurrImg}}),a("vs-button",{on:{click:function(e){return t.$refs.uploadImgInput.click()}}},[t._v("Upload Image")])],1)],2)]),a("div",{staticClass:"flex flex-wrap items-center p-6",attrs:{slot:"footer"},slot:"footer"},[a("vs-button",{staticClass:"mr-6",attrs:{disabled:!t.isFormValid},on:{click:t.submitData}},[t._v("Submit")]),a("vs-button",{attrs:{type:"border",color:"danger"},on:{click:function(e){t.isSidebarActiveLocal=!1}}},[t._v("Cancel")])],1)],1)},r=[],i=(a("7f7f"),a("ac6a"),a("ffc1"),a("9d63")),n=a.n(i),o={props:{isSidebarActive:{type:Boolean,required:!0},data:{type:Object,default:function(){}}},watch:{isSidebarActive:function(t){if(t)if(0===Object.entries(this.data).length)this.initValues(),this.$validator.reset();else{var e=JSON.parse(JSON.stringify(this.data)),a=e.category,s=e.id,r=e.img,i=e.name,n=e.order_status,o=e.price;this.dataId=s,this.dataCategory=a,this.dataImg=r,this.dataName=i,this.dataOrder_status=n,this.dataPrice=o,this.initValues()}}},data:function(){return{dataId:null,dataName:"",dataCategory:null,dataImg:null,dataOrder_status:"pending",dataPrice:0,category_choices:[{text:"Audio",value:"audio"},{text:"Computers",value:"computers"},{text:"Fitness",value:"fitness"},{text:"Appliance",value:"appliance"}],order_status_choices:[{text:"Pending",value:"pending"},{text:"Canceled",value:"canceled"},{text:"Delivered",value:"delivered"},{text:"On Hold",value:"on_hold"}],settings:{maxScrollbarLength:60,wheelSpeed:.6}}},computed:{isSidebarActiveLocal:{get:function(){return this.isSidebarActive},set:function(t){t||this.$emit("closeSidebar")}},isFormValid:function(){return!this.errors.any()&&this.dataName&&this.dataCategory&&this.dataPrice>0}},methods:{initValues:function(){this.data.id||(this.dataId=null,this.dataName="",this.dataCategory=null,this.dataOrder_status="pending",this.dataPrice=0,this.dataImg=null)},submitData:function(){var t=this;this.$validator.validateAll().then((function(e){if(e){var a={id:t.dataId,name:t.dataName,img:t.dataImg,category:t.dataCategory,order_status:t.dataOrder_status,price:t.dataPrice};null!==t.dataId&&t.dataId>=0?t.$store.dispatch("dataList/updateItem",a).catch((function(t){console.error(t)})):(delete a.id,a.popularity=0,t.$store.dispatch("dataList/addItem",a).catch((function(t){console.error(t)}))),t.$emit("closeSidebar"),t.initValues()}}))},updateCurrImg:function(t){var e=this;if(t.target.files&&t.target.files[0]){var a=new FileReader;a.onload=function(t){e.dataImg=t.target.result},a.readAsDataURL(t.target.files[0])}}},components:{VuePerfectScrollbar:n.a}},c=o,d=(a("83b2"),a("2877")),l=Object(d["a"])(c,s,r,!1,null,"189ab85c",null);e["a"]=l.exports},"7ddf":function(t,e,a){},"83b2":function(t,e,a){"use strict";a("7ddf")},9153:function(t,e,a){"use strict";a("2471")},ee31:function(t,e,a){"use strict";var s={products:[]},r=(a("20d6"),{ADD_ITEM:function(t,e){t.products.unshift(e)},SET_PRODUCTS:function(t,e){t.products=e},UPDATE_PRODUCT:function(t,e){var a=t.products.findIndex((function(t){return t.id==e.id}));Object.assign(t.products[a],e)},REMOVE_ITEM:function(t,e){var a=t.products.findIndex((function(t){return t.id==e}));t.products.splice(a,1)}}),i=a("bb36"),n={addItem:function(t,e){var a=t.commit;return new Promise((function(t,s){i["a"].post("/api/data-list/products/",{item:e}).then((function(s){a("ADD_ITEM",Object.assign(e,{id:s.data.id})),t(s)})).catch((function(t){s(t)}))}))},fetchDataListItems:function(t){var e=t.commit;return new Promise((function(t,a){i["a"].get("/api/data-list/products").then((function(a){e("SET_PRODUCTS",a.data),t(a)})).catch((function(t){a(t)}))}))},updateItem:function(t,e){var a=t.commit;return new Promise((function(t,s){i["a"].post("/api/data-list/products/".concat(e.id),{item:e}).then((function(e){a("UPDATE_PRODUCT",e.data),t(e)})).catch((function(t){s(t)}))}))},removeItem:function(t,e){var a=t.commit;return new Promise((function(t,s){i["a"].delete("/api/data-list/products/".concat(e)).then((function(s){a("REMOVE_ITEM",e),t(s)})).catch((function(t){s(t)}))}))}},o={};e["a"]={isRegistered:!1,namespaced:!0,state:s,mutations:r,actions:n,getters:o}}}]);
//# sourceMappingURL=chunk-81a5048a.717f1db7.js.map