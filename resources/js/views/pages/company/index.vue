<script>
import Layout from "../../layouts/main";
import PageHeader from "../../../components/Page-header";
import ErrorMessage from "../../../components/widgets/errorMessage";
import adminApi from "../../../api/adminAxios";
import { required, minLength, maxLength ,integer,email, sameAs ,url} from "vuelidate/lib/validators";
import Swal from "sweetalert2";
import loader from "../../../components/loader";
import {dynamicSortNumber, dynamicSortString} from "../../../helper/tableSort";
import Multiselect from "vue-multiselect";

/**
 * Advanced Table component
 */

// start validation image/png

export default {
    page: {
        title: "Companies",
        meta: [{ name: "description", content: 'Companies' }],
    },
    components: {
        Layout,
        PageHeader,
        loader,
        ErrorMessage,
        Multiselect
    },
    data() {
        return {
            per_page: 50,
            search: '',
            debounce: {},
            companiesPagination: {},
            companies: [],
            partners: [],
            enabled3: false,
            isLoader: false,
            create: {
                name: '',
                name_e: '',
                email: '',
                partner_id: null,
                url:'',
                phone:'',
                tax_id: null,
                vat_no: null,
                cr:'',
                address:'',
                website: '',
                phone_code:'',
                country_code:'',
                is_active: null,
            },
            edit: {
                name: '',
                name_e: '',
                email: '',
                partner_id: null,
                url:'',
                phone:'',
                tax_id: null,
                vat_no: null,
                cr:'',
                address:'',
                website: '',
                phone_code:'',
                country_code:'',
                is_active: null,
                old_media: []
            },
            errors: {},
            isCheckAll: false,
            checkAll: [],
            current_page: 1,
            images: [],
            media: {},
            company_id: null,
            isVaildPhone: false,
            saveImageName: [],
            showPhoto: './images/img-1.png',
            setting: {
                name:true,
                name_e:true,
                email: true,
                partner_id: true,
                url: true,
                phone: true,
                tax_id: true,
                vat_no: true,
                cr: true,
                address: true,
                website: true,
                is_active: true
            },
            idDelete: null,
            filterSetting: ['name', 'name_e', 'email', 'partner_id', 'url', 'phone', 'tax_id','vat_no','cr','address','website']
        }
    },
    validations: {
        create: {
            name: {required,minLength: minLength(3),maxLength: maxLength(100)},
            name_e: {required,minLength: minLength(3),maxLength: maxLength(100)},
            email: {required,email,minLength: minLength(3),maxLength: maxLength(100)},
            phone: {required,integer},
            tax_id: {required,minLength: minLength(1),maxLength: maxLength(10),integer},
            vat_no: {required,minLength: minLength(1),maxLength: maxLength(10),integer},
            url: {required,url,minLength: minLength(10),maxLength: maxLength(200)},
            website: {required,url,minLength: minLength(10),maxLength: maxLength(200)},
            cr: {required,minLength: minLength(3),maxLength: maxLength(100)},
            address: {required,minLength: minLength(10),maxLength: maxLength(200)},
            partner_id: {required,integer},
            is_active: {required},
        },
        edit: {
            name: {required,minLength: minLength(3),maxLength: maxLength(100)},
            name_e: {required,minLength: minLength(3),maxLength: maxLength(100)},
            email: {required,email,minLength: minLength(3),maxLength: maxLength(100)},
            phone: {required,integer},
            tax_id: {required,minLength: minLength(1),maxLength: maxLength(10),integer},
            vat_no: {required,minLength: minLength(1),maxLength: maxLength(10),integer},
            url: {required,url,minLength: minLength(10),maxLength: maxLength(200)},
            website: {required,url,minLength: minLength(10),maxLength: maxLength(200)},
            cr: {required,minLength: minLength(3),maxLength: maxLength(100)},
            address: {required,minLength: minLength(10),maxLength: maxLength(200)},
            partner_id: {required,integer},
            is_active: {required}
        },
    },
    watch: {
        /**
         * watch per_page
         */
        per_page(after,befour){
            this.getData();
        },
        /**
         * watch search
         */
        search(after,befour){
            clearTimeout(this.debounce);
            this.debounce = setTimeout(() => {
                this.getData();
            }, 400);
        },
        /**
         * watch check All table
         */
        isCheckAll(after,befour){
            if(after){
                this.companies.forEach(el => {
                    if(!this.checkAll.includes(el.id)){
                        this.checkAll.push(el.id);
                    }
                });
            }else{
                this.checkAll = [];
            }
        }
    },
    mounted() {
        this.getData();
    },
    updated(){
        $(function(){
            $(".englishInput").keypress(function(event){
                var ew = event.which;
                if(ew == 32)
                    return true;
                if(48 <= ew && ew <= 57)
                    return true;
                if(65 <= ew && ew <= 90)
                    return true;
                if(97 <= ew && ew <= 122)
                    return true;
                return false;
            });
            $(".arabicInput").keypress(function(event){
                var ew = event.which;
                if(ew == 32)
                    return true;
                if(48 <= ew && ew <= 57)
                    return false;
                if(65 <= ew && ew <= 90)
                    return false;
                if(97 <= ew && ew <= 122)
                    return false;
                return true;
            });
        });
    },
    methods: {
        /**
         *  get Data company
         */
        getData(page = 1){

            this.isLoader = true;
            let filter = '';
            for (let i = 0; i > this.filterSetting.length; ++i) {
                filter += `columns[${i}]=${this.filterSetting[i]}&`;
            }

            adminApi.get(`/companies?page=${page}&per_page=${this.per_page}&search=${this.search}&${filter}`)
                .then((res) => {
                    let l = res.data;
                    this.companies = l.data;
                    this.companiesPagination = l.pagination;
                    this.current_page = l.pagination.current_page;
                })
                .catch((err) => {
                    Swal.fire({
                        icon: 'error',
                        title: `${this.$t('general.Error')}`,
                        text: `${this.$t('general.Thereisanerrorinthesystem')}`,
                    });
                })
                .finally(() => {
                    this.isLoader = false;
                });
        },
        getDataCurrentPage(){
            if(this.current_page <= this.companiesPagination.last_page && this.current_page != this.companiesPagination.current_page && this.current_page){
                this.isLoader = true;
                let filter = '';
                for (let i = 0; i > this.filterSetting.length; ++i) {
                    filter += `columns[${i}]=${this.filterSetting[i]}&`;
                }

                adminApi.get(`/companies?page=${this.current_page}&per_page=${this.per_page}&search=${this.search}&${filter}`)
                    .then((res) => {
                        let l = res.data;
                        this.companies = l.data;
                        this.companiesPagination = l.pagination;
                        this.current_page = l.pagination.current_page;
                        this.current_page = l.pagination.current_page;
                    })
                    .catch((err) => {
                        Swal.fire({
                            icon: 'error',
                            title: `${this.$t('general.Error')}`,
                            text: `${this.$t('general.Thereisanerrorinthesystem')}`,
                        });
                    })
                    .finally(() => {
                        this.isLoader = false;
                    });
            }
        },
        /**
         *  delete company
         */
        deletecompany(id,index) {
            Swal.fire({
                title: `${this.$t('general.Areyousure')}`,
                text: `${this.$t('general.Youwontbeabletoreverthis')}`,
                type: "warning",
                showCancelButton: true,
                confirmButtonText: `${this.$t('general.Yesdeleteit')}`,
                cancelButtonText: `${this.$t('general.Nocancel')}`,
                confirmButtonClass: "btn btn-success mt-2",
                cancelButtonClass: "btn btn-danger ml-2 mt-2",
                buttonsStyling: false,
            }).then((result) => {
                if (result.value) {

                    this.isLoader = true;

                    adminApi.delete(`/companies/${id}`)
                        .then((res) => {
                            this.checkAll = [];
                            this.getData();
                            Swal.fire({
                                icon: 'success',
                                title: `${this.$t('general.Deleted')}`,
                                text: `${this.$t('general.Yourrowhasbeendeleted')}`,
                                showConfirmButton: false,
                                timer: 1500
                            });
                        })
                        .catch((err) => {
                            Swal.fire({
                                icon: 'error',
                                title: `${this.$t('general.Error')}`,
                                text: `${this.$t('general.Thereisanerrorinthesystem')}`,
                            });
                        })
                        .finally(() => {
                            this.isLoader = false;
                        });
                }
            });
        },
        /**
         *  reset Modal (create)
         */
        resetModalHidden(){
            this.create =  {
                name: '',
                name_e: '',
                email: '',
                partner_id: null,
                url:'',
                phone:'',
                tax_id: null,
                vat_no: null,
                cr:'',
                address:'',
                website: '',
                phone_code:'',
                country_code:'',
                is_active: null,
            };
            this.$nextTick(() => { this.$v.$reset() });
            this.errors = {};
            this.isDrop = false;
            this.image = '';
            this.dropDownSenders = [];
            this.$bvModal.hide(`create`);
        },
        /**
         *  hidden Modal (create)
         */
        async resetModal(){
            await this.getPartner();
            this.create =  {
                name: '',
                name_e: '',
                email: '',
                partner_id: null,
                url:'',
                phone:'',
                tax_id: null,
                vat_no: null,
                cr:'',
                address:'',
                website: '',
                phone_code:'',
                country_code:'',
                is_active: 'active',
            };
            this.$nextTick(() => { this.$v.$reset() });
            this.errors = {};
            this.company_id = null;
        },
        /**
         *  create company
         */
        resetForm(){
            this.create =  {
                name: '',
                name_e: '',
                email: '',
                partner_id: null,
                url:'',
                phone:'',
                tax_id: null,
                vat_no: null,
                cr:'',
                address:'',
                website: '',
                phone_code:'',
                country_code:'',
                is_active: 'active',
            };
            this.$nextTick(() => { this.$v.$reset() });
            this.company_id = null;
            this.isVaildPhone = false;
        },
        AddSubmit(){
            this.$v.create.$touch();

            if (this.$v.create.$invalid && !this.isVaildPhone) {
                return;
            } else {
                this.isLoader = true;
                this.errors = {};
                if(!this.create.name){ this.create.name = this.create.name_e}
                if(!this.create.name_e){ this.create.name_e = this.create.name}

                adminApi.post(`/companies`,this.create)
                    .then((res) => {
                        this.company_id = res.data.data.id;
                        this.getData();
                        setTimeout(() => {
                            Swal.fire({
                                icon: 'success',
                                text: `${this.$t('general.Addedsuccessfully')}`,
                                showConfirmButton: false,
                                timer: 1500
                            });
                        },500);
                    })
                    .catch((err) => {
                        if(err.response.data){
                            this.errors = err.response.data.errors;
                        }else {
                            Swal.fire({
                                icon: 'error',
                                title: `${this.$t('general.Error')}`,
                                text: `${this.$t('general.Thereisanerrorinthesystem')}`,
                            });
                        }
                    }).finally(() => {
                        this.isLoader = false;
                    });
            }

        },
        /**
         *  edit company
         */
        editSubmit(id,index){
            this.$v.edit.$touch();
            if(this.images) this.images.forEach(e => {this.edit.old_media.push(e.id);});
            if (this.$v.edit.$invalid && !this.isVaildPhone) {
                return;
            } else {
                this.isLoader = true;
                this.errors = {};

                adminApi.post(`/companies/${id}`, this.edit)
                    .then((res) => {
                        this.getData();
                        this.$bvModal.hide(`modal-edit-${id}`);
                        setTimeout(() => {
                            Swal.fire({
                                icon: 'success',
                                text: `${this.$t('general.Editsuccessfully')}`,
                                showConfirmButton: false,
                                timer: 1500
                            });
                        },500);
                    })
                    .catch((err) => {
                        if(err.response.data){
                            this.errors = err.response.data.errors;
                        }else {
                            Swal.fire({
                                icon: 'error',
                                title: `${this.$t('general.Error')}`,
                                text: `${this.$t('general.Thereisanerrorinthesystem')}`,
                            });
                        }
                    }).finally(() => {
                        this.isLoader = false;
                    });
            }
        },
        /**
         *  get parent
         */
        async getPartner(){
            await adminApi.get(`/partners`)
                .then((res) => {
                    let l = res.data;
                    this.partners = l.data;
                })
                .catch((err) => {
                    Swal.fire({
                        icon: 'error',
                        title: `${this.$t('general.Error')}`,
                        text: `${this.$t('general.Thereisanerrorinthesystem')}`,
                    });
                });
        },
        /**
         *   show Modal (edit)
         */
        async resetModalEdit(id){
            let company = this.companies.find(e => id == e.id );
            await this.getPartner();
            this.company_id = company.id;
            this.isVaildPhone = false;
            this.company_id = company.id;
            this.edit.name = company.name;
            this.edit.name_e = company.name_e;
            this.edit.is_active = company.is_active;
            this.edit.email = company.email;
            this.edit.address = company.address;
            this.edit.cr = company.cr;
            this.edit.partner_id = company.partner.id;
            this.edit.tax_id = company.tax_id;
            this.edit.url = company.url;
            this.edit.website = company.website;
            this.edit.vat_no = company.vat_no;
            this.edit.phone = company.phone;
            this.errors = {};
            this.images = company.media ?? [];
            if(this.images.length > 0) this.showPhoto = this.images[this.images.length - 1].webp;
        },
        /**
         *  hidden Modal (edit)
         */
        resetModalHiddenEdit(id){
            this.edit = {
                name: '',
                name_e: '',
                email: '',
                partner_id: null,
                url:'',
                phone:'',
                tax_id: null,
                vat_no: null,
                cr:'',
                address:'',
                website: '',
                phone_code:'',
                country_code:'',
                is_active: 'active',
                old_media: []
            };
            this.errors = {};
            this.company_id = null;
        },
        /**
         *  update phone
         */
        updatePhone(e){
            this.create.phone_code = e.countryCallingCode;
            this.create.country_code = e.countryCode;
            this.isVaildPhone = e.isValid;
        },
        updatePhoneEdit(e){
            this.edit.phone_code = e.countryCallingCode;
            this.edit.country_code = e.countryCode;
            this.isVaildPhone = e.isValid;
        },
        /**
         *  start  dynamicSortString
         */
        /**
         *  start  dynamicSortString
         */
        sortString(value){
            return dynamicSortString(value);
        },
        sortNumber(value){
            return dynamicSortNumber(value);
        },
        checkRow(id){
            if(!this.checkAll.includes(id)) {
                this.checkAll.push(id);
            }else {
                let index = this.checkAll.indexOf(id);
                this.checkAll.splice(index,1);
            }
        },
        /**
         *  start Image ceate
         */
        changePhoto(){document.getElementById('uploadImageCreate').click();},
        changePhotoEdit(){document.getElementById('uploadImageEdit').click();},
        onImageChanged(e){
            const file = e.target.files[0];
            this.addImage(file);
        },
        addImage(file){
            this.media = file; //upload
            if(file){

                this.idDelete = null;
                let is_media = this.images.find(e =>  e.name == file.name.slice(0,file.name.indexOf('.')));
                this.idDelete = is_media? is_media.id: null;
                if(!this.idDelete){
                    this.isLoader = true;
                    let formDate = new FormData();
                    formDate.append('media[0]',this.media);
                    adminApi.post(`/media`, formDate)
                        .then((res) => {
                            let old_media = [];
                            if(this.images) this.images.forEach(e => old_media.push(e.id));
                            let new_media = [];
                            res.data.data.forEach(e => new_media.push(e.id));

                            adminApi.post(`/companies/${this.company_id}`,{old_media,'media':new_media})
                                .then((res) => {
                                    this.images = res.data.data.media;
                                    this.showPhoto = this.images[this.images.length - 1].webp;
                                    this.getData();
                                })
                                .catch(err => {
                                    Swal.fire({
                                        icon: 'error',
                                        title: `${this.$t('general.Error')}`,
                                        text: `${this.$t('general.Thereisanerrorinthesystem')}`,
                                    });
                                });
                        })
                        .catch((err) => {
                            if (err.response.data) {
                                this.errors = err.response.data.errors;
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: `${this.$t('general.Error')}`,
                                    text: `${this.$t('general.Thereisanerrorinthesystem')}`,
                                });
                            }
                        }).finally(() => {
                        this.isLoader = false;
                    });
                }else {
                    Swal.fire({
                        title: `${this.$t('general.Thisfilehasalreadybeenuploaded')}`,
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonText: `${this.$t('general.Replace')}`,
                        cancelButtonText: `${this.$t('general.Nocancel')}`,
                        confirmButtonClass: "btn btn-success mt-2",
                        cancelButtonClass: "btn btn-danger ml-2 mt-2",
                        buttonsStyling: false,
                    }).then((result) => {
                        if (result.value) {

                            this.isLoader = true;
                            let formDate = new FormData();
                            formDate.append('media[0]',this.media);
                            adminApi.post(`/media`, formDate)
                                .then((res) => {
                                    let old_media = [];
                                    this.images.forEach(e => old_media.push(e.id));
                                    old_media.splice(old_media.indexOf(this.idDelete),1);
                                    let new_media = [];
                                    res.data.data.forEach(e => new_media.push(e.id));

                                    adminApi.post(`/companies/${this.company_id}`,{old_media,'media':new_media})
                                        .then((res) => {
                                            this.images = res.data.data.media;
                                            this.showPhoto = this.images[this.images.length - 1].webp;
                                            this.getData();
                                        })
                                        .catch(err => {
                                            Swal.fire({
                                                icon: 'error',
                                                title: `${this.$t('general.Error')}`,
                                                text: `${this.$t('general.Thereisanerrorinthesystem')}`,
                                            });
                                        });
                                })
                                .catch((err) => {
                                    if (err.response.data) {
                                        this.errors = err.response.data.errors;
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: `${this.$t('general.Error')}`,
                                            text: `${this.$t('general.Thereisanerrorinthesystem')}`,
                                        });
                                    }
                                }).finally(() => {
                                this.isLoader = false;
                            });

                        }
                    });
                }
            }
        },
        deleteImageCreate(id,index){
            let old_media = [];
            this.images.forEach(e => {
                if(e.id != id){
                    old_media.push(e.id);
                }
            });
            adminApi.post(`/companies/${this.company_id}`,{old_media})
                .then((res) => {
                    this.images = res.data.data.media ?? [];
                    if(this.images.length){
                        this.showPhoto = this.images[this.images.length - 1].webp;
                    }
                })
                .catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: `${this.$t('general.Error')}`,
                        text: `${this.$t('general.Thereisanerrorinthesystem')}`,
                    });
                });
        }
        /**
         *  end Image ceate
         */
    }
};
</script>

<template>
    <Layout>
        <PageHeader />
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        <div class="row justify-content-between align-items-center mb-2">
                            <h4 class="header-title"> {{ $t('company.companysTable') }}</h4>
                            <div class="col-xs-10 col-md-9 col-lg-7" style="font-weight: 500">
                                <div class="d-inline-block" style="width: 22.2%;">
                                    <!-- Basic dropdown -->
                                    <b-dropdown variant="primary" :text="$t('general.searchSetting')" ref="dropdown" class="btn-block setting-search dropdown-menu-custom-company">
                                        <b-form-checkbox v-model="filterSetting" value="name" class="mb-1">
                                            {{ $t('general.Name') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="filterSetting" value="name_e" class="mb-1">
                                            {{ $t('general.Name_en') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="filterSetting" value="email" class="mb-1">
                                            {{ $t('login.Emailaddress') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="filterSetting" value="partner_id" class="mb-1">
                                            {{ $t('partner.partner') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="filterSetting" value="url" class="mb-1">
                                            {{ $t('general.url') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="filterSetting" value="phone" class="mb-1">
                                            {{ $t('general.mobile_no') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="filterSetting" value="tax_id"
                                                         class="mb-1">{{ $t('general.taxnumber') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="filterSetting" value="cr"
                                                         class="mb-1">{{ $t('general.CommercialRecord') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="filterSetting" value="vat_no"
                                                class="mb-1">{{ $t('general.Valueaddedregistrationnumber') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="filterSetting" value="address"
                                                         class="mb-1">{{ $t('general.address') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="filterSetting" value="vat_no"
                                                         class="mb-1">{{ $t('general.website') }}
                                        </b-form-checkbox>
                                    </b-dropdown>
                                    <!-- Basic dropdown -->
                                </div>

                                <div class="d-inline-block position-relative" style="width: 77%;">
                                    <span
                                        :class="['search-custom position-absolute',$i18n.locale == 'ar'?'search-custom-ar':'']"
                                    >
                                        <i class="fe-search"></i>
                                    </span>
                                    <input
                                        class="form-control"
                                        style="display:block;width:93%;padding-top: 3px;"
                                        type="text"
                                        v-model.trim="search"
                                        :placeholder="`${$t('general.Search')}...`"
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="row justify-content-between align-items-center mb-2 px-1">
                            <div class="col-md-3 d-flex align-items-center mb-1 mb-xl-0">
                                <b-button
                                    v-b-modal.create
                                    variant="primary"
                                    class="btn-sm mx-1 font-weight-bold"
                                >
                                    {{ $t('general.Create') }}
                                    <i class="fas fa-plus"></i>
                                </b-button>
                                <div class="d-inline-flex">
                                    <button class="custom-btn-dowonload">
                                        <i class="fas fa-file-download"></i>
                                    </button>
                                    <button class="custom-btn-dowonload">
                                        <i class="fe-printer"></i>
                                    </button>
                                    <button
                                        class="custom-btn-dowonload"
                                        @click="$bvModal.show(`modal-edit-${checkAll[0]}`)"
                                        v-if="checkAll.length == 1"
                                    >
                                        <i class="mdi mdi-square-edit-outline"></i>
                                    </button>
                                    <!-- start mult delete  -->
                                    <button
                                        class="custom-btn-dowonload"
                                        v-if="checkAll.length > 1"
                                        @click.prevent="deletecompany(checkAll)"
                                    >
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <!-- end mult delete  -->
                                    <!--  start one delete  -->
                                    <button
                                        class="custom-btn-dowonload"
                                        v-if="checkAll.length == 1"
                                        @click.prevent="deletecompany(checkAll)"
                                    >
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <!--  end one delete  -->
                                </div>
                            </div>
                            <div
                                class="col-xs-10 col-md-9 col-lg-7 d-flex align-items-center  justify-content-end"
                            >
                                <div>
                                    <b-button
                                        class="mx-1 custom-btn-background"
                                    >
                                        {{ $t('general.filter') }}
                                        <i class="fas fa-filter"></i>
                                    </b-button>
                                    <b-button
                                        class="mx-1 custom-btn-background"
                                    >
                                        {{ $t('general.group') }}
                                        <i class="fe-menu"></i>
                                    </b-button>
                                    <b-dropdown variant="primary"
                                        :html="`${$t('general.setting')} <i class='fe-settings'></i>`"
                                        ref="dropdown" class="dropdown-custom-ali dropdown-menu-custom-company"
                                    >
                                        <b-form-checkbox v-model="setting.name" class="mb-1">{{
                                                $t('general.Name')
                                            }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="setting.name_e" class="mb-1">
                                            {{ $t('general.Name_en') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="setting.email" class="mb-1">
                                            {{ $t('login.Emailaddress') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="setting.address" class="mb-1">
                                            {{ $t('general.address') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="setting.phone" class="mb-1">
                                            {{ $t('general.mobile_no') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="setting.partner_id" class="mb-1">
                                            {{ $t('partner.partner') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="setting.cr" class="mb-1">
                                            {{ $t('general.CommercialRecord') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="setting.tax_id" class="mb-1">
                                            {{ $t('general.taxnumber') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="setting.is_active" class="mb-1">
                                            {{ $t('general.Status') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="setting.vat_no" class="mb-1">
                                            {{ $t('general.Valueaddedregistrationnumber') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="setting.website" class="mb-1">
                                            {{ $t('general.website') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="setting.url" class="mb-1">
                                            {{ $t('general.companysystempath') }}
                                        </b-form-checkbox>
                                        <div class="d-flex justify-content-end">
                                            <a href="javascript:void(0)" class="btn btn-primary btn-sm">Apply</a>
                                        </div>
                                    </b-dropdown>
                                    <!-- start Pagination -->
                                    <div class="d-inline-flex align-items-center pagination-custom">
                                        <div class="d-inline-block" style="font-size:15px;">
                                            {{ companiesPagination.from }}-{{ companiesPagination.to }} / {{ companiesPagination.total }}
                                        </div>
                                        <div class="d-inline-block">
                                            <a
                                                href="javascript:void(0)"
                                                :style="{'pointer-events':companiesPagination.current_page == 1 ? 'none': ''}"
                                                @click.prevent="getData(companiesPagination.current_page - 1)"
                                            >
                                                <span>&lt;</span>
                                            </a>
                                            <input
                                                type="text"
                                                @keyup.enter="getDataCurrentPage()"
                                                v-model="current_page"
                                                class="pagination-current-page"
                                            />
                                            <a
                                                href="javascript:void(0)"
                                                :style="{'pointer-events':companiesPagination.last_page == companiesPagination.current_page ? 'none': ''}"
                                                @click.prevent="getData(companiesPagination.current_page + 1)"
                                            >
                                                <span>&gt;</span>
                                            </a>
                                        </div>
                                    </div>
                                    <!-- end Pagination -->
                                </div>
                            </div>
                        </div>

                        <!--  create   -->
                        <b-modal
                            id="create"
                            :title="$t('company.addcompany')"
                            title-class="font-18"
                            dialog-class="modal-full-width"
                            body-class="paddingUnset"
                            :hide-footer="true"
                            @show="resetModal"
                            @hidden="resetModalHidden"
                        >
                            <form  @submit.stop.prevent="AddSubmit">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="mt-1 d-flex justify-content-end">
                                            <!-- Emulate built in modal footer ok and cancel button actions -->
                                            <b-button
                                                variant="success"
                                                :disabled="!company_id"
                                                @click.prevent="resetForm"
                                                type="button" :class="['font-weight-bold px-2',company_id?'mx-2': '']"
                                            >
                                                {{ $t('general.AddNewRecord') }}
                                            </b-button>

                                            <template v-if="!company_id">
                                                <b-button
                                                    variant="success"
                                                    type="button" class="mx-1 font-weight-bold px-3"
                                                    v-if="!isLoader"
                                                    @click.prevent="AddSubmit"
                                                >
                                                    {{ $t('general.Save') }}
                                                </b-button>

                                                <b-button variant="success" class="mx-1" disabled v-else>
                                                    <b-spinner small></b-spinner>
                                                    <span class="sr-only">{{ $t('login.Loading') }}...</span>
                                                </b-button>

                                            </template>

                                            <b-button variant="danger" class="font-weight-bold" type="button" @click.prevent="resetModalHidden">
                                                {{ $t('general.Cancel') }}
                                            </b-button>
                                        </div>
                                    </div>
                                    <b-tabs nav-class="nav-tabs nav-bordered">
                                        <b-tab :title="$t('general.DataEntry')" active>
                                            <div class="row justify-content-between">
                                                <div class='col-7'>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group position-relative">
                                                                <label class="control-label">
                                                                    {{ $t('partner.partner') }}
                                                                    <span class="text-danger">*</span>
                                                                </label>

                                                                <multiselect
                                                                    v-model="create.partner_id"
                                                                    :options="partners.map(type => type.id)"
                                                                    :custom-label="opt => $i18n.locale ? partners.find(x => x.id == opt).name : partners.find(x => x.id == opt).name_e">
                                                                </multiselect>

                                                                <div v-if="!$v.create.partner_id.integer" class="invalid-feedback">{{ $t('general.fieldIsInteger') }}</div>
                                                                <template v-if="errors.partner_id">
                                                                    <ErrorMessage v-for="(errorMessage,index) in errors.partner_id" :key="index">{{ errorMessage }}</ErrorMessage>
                                                                </template>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6"></div>
                                                        <div class="col-md-6 direction" dir="rtl">
                                                            <div class="form-group">
                                                                <label for="field-1" class="control-label">
                                                                    {{ $t('general.Name') }}
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <input
                                                                    type="text"
                                                                    class="form-control arabicInput"
                                                                    v-model.trim="$v.create.name.$model"
                                                                    :class="{
                                                                        'is-invalid':$v.create.name.$error || errors.name,
                                                                        'is-valid':!$v.create.name.$invalid && !errors.name
                                                                    }"
                                                                    id="field-1"
                                                                />
                                                                <div v-if="!$v.create.name.minLength" class="invalid-feedback">{{ $t('general.Itmustbeatleast') }} {{ $v.create.name.$params.minLength.min }} {{ $t('general.letters') }}</div>
                                                                <div v-if="!$v.create.name.maxLength" class="invalid-feedback">{{ $t('general.Itmustbeatmost') }}  {{ $v.create.name.$params.maxLength.max }} {{ $t('general.letters') }}</div>
                                                                <template v-if="errors.name">
                                                                    <ErrorMessage v-for="(errorMessage,index) in errors.name" :key="index">{{ errorMessage }}</ErrorMessage>
                                                                </template>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 direction-ltr" dir="ltr">
                                                            <div class="form-group">
                                                                <label for="field-2" class="control-label">
                                                                    {{ $t('general.Name_en') }}
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <input
                                                                    type="text"
                                                                    class="form-control englishInput"
                                                                    v-model.trim="$v.create.name_e.$model"
                                                                    :class="{
                                                                        'is-invalid':$v.create.name_e.$error || errors.name_e,
                                                                        'is-valid':!$v.create.name_e.$invalid && !errors.name_e
                                                                    }"
                                                                    id="field-2"
                                                                />
                                                                <div v-if="!$v.create.name_e.minLength" class="invalid-feedback">{{ $t('general.Itmustbeatleast') }} {{ $v.create.name_e.$params.minLength.min }} {{ $t('general.letters') }}</div>
                                                                <div v-if="!$v.create.name_e.maxLength" class="invalid-feedback">{{ $t('general.Itmustbeatmost') }}  {{ $v.create.name_e.$params.maxLength.max }} {{ $t('general.letters') }}</div>
                                                                <template v-if="errors.name_e">
                                                                    <ErrorMessage v-for="(errorMessage,index) in errors.name_e" :key="index">{{ errorMessage }}</ErrorMessage>
                                                                </template>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="field-3" class="control-label">
                                                                    {{ $t('login.Emailaddress') }}
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <input
                                                                    type="email"
                                                                    class="form-control"
                                                                    v-model.trim="$v.create.email.$model"
                                                                    :class="{
                                                            'is-invalid':$v.create.email.$error || errors.email,
                                                            'is-valid':!$v.create.email.$invalid && !errors.email
                                                        }"
                                                                    id="field-3"
                                                                />
                                                                <div v-if="!$v.create.email.email" class="invalid-feedback">{{ $t('general.PleaseEnterValidEmail') }}</div>
                                                                <div v-if="!$v.create.email.minLength" class="invalid-feedback">{{ $t('general.Itmustbeatleast') }} {{ $v.create.email.$params.minLength.min }} {{ $t('general.letters') }}</div>
                                                                <div v-if="!$v.create.email.maxLength" class="invalid-feedback">{{ $t('general.Itmustbeatmost') }}  {{ $v.create.email.$params.maxLength.max }} {{ $t('general.letters') }}</div>
                                                                <template v-if="errors.email">
                                                                    <ErrorMessage v-for="(errorMessage,index) in errors.email" :key="index">{{ errorMessage }}</ErrorMessage>
                                                                </template>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="control-label">
                                                                    {{ $t('general.mobile_no') }}
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <VuePhoneNumberInput
                                                                    v-model="$v.create.phone.$model"
                                                                    default-country-code="EG"
                                                                    valid-color="#28a745"
                                                                    error-color="#dc3545"
                                                                    :preferred-countries="['FR', 'EG', 'DE']"
                                                                    @update="updatePhone"
                                                                />
                                                                <template v-if="errors.phone">
                                                                    <ErrorMessage v-for="(errorMessage,index) in errors.phone" :key="index">{{ errorMessage }}</ErrorMessage>
                                                                </template>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="field-12" class="control-label">
                                                                    {{ $t('general.address') }}
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <input
                                                                    type="text"
                                                                    class="form-control"
                                                                    v-model.number="$v.create.address.$model"
                                                                    :class="{
                                                            'is-invalid':$v.create.address.$error || errors.address,
                                                            'is-valid':!$v.create.address.$invalid && !errors.address
                                                        }"
                                                                    id="field-12"
                                                                />
                                                                <div v-if="!$v.create.address.minLength" class="invalid-feedback">{{ $t('general.Itmustbeatleast') }} {{ $v.create.address.$params.minLength.min }} {{ $t('general.letters') }}</div>
                                                                <div v-if="!$v.create.address.maxLength" class="invalid-feedback">{{ $t('general.Itmustbeatmost') }}  {{ $v.create.address.$params.maxLength.max }} {{ $t('general.letters') }}</div>
                                                                <template v-if="errors.address">
                                                                    <ErrorMessage v-for="(errorMessage,index) in errors.address" :key="index">{{ errorMessage }}</ErrorMessage>
                                                                </template>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="field-11" class="control-label">
                                                                    {{ $t('general.website') }}
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <input
                                                                    type="url"
                                                                    class="form-control"
                                                                    v-model.number="$v.create.website.$model"
                                                                    :class="{
                                                            'is-invalid':$v.create.website.$error || errors.website,
                                                            'is-valid':!$v.create.website.$invalid && !errors.website
                                                        }"
                                                                    id="field-11"
                                                                />
                                                                <div v-if="!$v.create.website.minLength" class="invalid-feedback">{{ $t('general.Itmustbeatleast') }} {{ $v.create.website.$params.minLength.min }} {{ $t('general.letters') }}</div>
                                                                <div v-if="!$v.create.website.maxLength" class="invalid-feedback">{{ $t('general.Itmustbeatmost') }}  {{ $v.create.website.$params.maxLength.max }} {{ $t('general.letters') }}</div>
                                                                <div v-if="!$v.create.website.url" class="invalid-feedback">{{ $t('general.Itmustbeyourlink') }}</div>
                                                                <template v-if="errors.website">
                                                                    <ErrorMessage v-for="(errorMessage,index) in errors.website" :key="index">{{ errorMessage }}</ErrorMessage>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class='col-4'>
                                                    <div class="row">
                                                        <div class="col-md-6" style="height: 67.6px;"></div>
                                                        <div class="col-md-6" style="height: 67.6px;"></div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="field-5" class="control-label">
                                                                    {{ $t('general.taxnumber') }}
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <input
                                                                    type="number"
                                                                    class="form-control"
                                                                    v-model.number="$v.create.tax_id.$model"
                                                                    :class="{
                                                            'is-invalid':$v.create.tax_id.$error || errors.tax_id,
                                                            'is-valid':!$v.create.tax_id.$invalid && !errors.tax_id
                                                        }"
                                                                    id="field-5"
                                                                />
                                                                <div v-if="!$v.create.tax_id.minLength" class="invalid-feedback">{{ $t('general.Itmustbeatleast') }} {{ $v.create.tax_id.$params.minLength.min }} {{ $t('general.letters') }}</div>
                                                                <div v-if="!$v.create.tax_id.maxLength" class="invalid-feedback">{{ $t('general.Itmustbeatmost') }}  {{ $v.create.tax_id.$params.maxLength.max }} {{ $t('general.letters') }}</div>
                                                                <div v-if="!$v.create.tax_id.integer" class="invalid-feedback">{{ $t('general.fieldIsInteger') }}</div>
                                                                <template v-if="errors.tax_id">
                                                                    <ErrorMessage v-for="(errorMessage,index) in errors.tax_id" :key="index">{{ errorMessage }}</ErrorMessage>
                                                                </template>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="field-8" class="control-label">
                                                                    {{ $t('general.CommercialRecord') }}
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <input
                                                                    type="text"
                                                                    class="form-control"
                                                                    v-model.trim="$v.create.cr.$model"
                                                                    :class="{
                                                            'is-invalid':$v.create.cr.$error || errors.cr,
                                                            'is-valid':!$v.create.cr.$invalid && !errors.cr
                                                        }"
                                                                    id="field-8"
                                                                />
                                                                <div v-if="!$v.create.cr.minLength" class="invalid-feedback">{{ $t('general.Itmustbeatleast') }} {{ $v.create.cr.$params.minLength.min }} {{ $t('general.letters') }}</div>
                                                                <div v-if="!$v.create.cr.maxLength" class="invalid-feedback">{{ $t('general.Itmustbeatmost') }}  {{ $v.create.cr.$params.maxLength.max }} {{ $t('general.letters') }}</div>
                                                                <template v-if="errors.cr">
                                                                    <ErrorMessage v-for="(errorMessage,index) in errors.cr" :key="index">{{ errorMessage }}</ErrorMessage>
                                                                </template>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="field-10" class="control-label">
                                                                    {{ $t('general.companysystempath') }}
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <input
                                                                    type="url"
                                                                    class="form-control"
                                                                    v-model.number="$v.create.url.$model"
                                                                    :class="{
                                                            'is-invalid':$v.create.url.$error || errors.url,
                                                            'is-valid':!$v.create.url.$invalid && !errors.url
                                                        }"
                                                                    id="field-10"
                                                                />
                                                                <div v-if="!$v.create.url.minLength" class="invalid-feedback">{{ $t('general.Itmustbeatleast') }} {{ $v.create.url.$params.minLength.min }} {{ $t('general.letters') }}</div>
                                                                <div v-if="!$v.create.url.maxLength" class="invalid-feedback">{{ $t('general.Itmustbeatmost') }}  {{ $v.create.url.$params.maxLength.max }} {{ $t('general.letters') }}</div>
                                                                <div v-if="!$v.create.url.url" class="invalid-feedback">{{ $t('general.Itmustbeyourlink') }}</div>
                                                                <template v-if="errors.url">
                                                                    <ErrorMessage v-for="(errorMessage,index) in errors.url" :key="index">{{ errorMessage }}</ErrorMessage>
                                                                </template>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="field-6" class="control-label" style="font-size: 10px;">
                                                                    {{ $t('general.Valueaddedregistrationnumber') }}
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <input
                                                                    type="number"
                                                                    class="form-control"
                                                                    v-model.number="$v.create.vat_no.$model"
                                                                    :class="{
                                                            'is-invalid':$v.create.vat_no.$error || errors.vat_no,
                                                            'is-valid':!$v.create.vat_no.$invalid && !errors.vat_no
                                                        }"
                                                                    id="field-6"
                                                                />
                                                                <div v-if="!$v.create.vat_no.minLength" class="invalid-feedback">{{ $t('general.Itmustbeatleast') }} {{ $v.create.vat_no.$params.minLength.min }} {{ $t('general.letters') }}</div>
                                                                <div v-if="!$v.create.vat_no.maxLength" class="invalid-feedback">{{ $t('general.Itmustbeatmost') }}  {{ $v.create.vat_no.$params.maxLength.max }} {{ $t('general.letters') }}</div>
                                                                <div v-if="!$v.create.vat_no.integer" class="invalid-feedback">{{ $t('general.fieldIsInteger') }}</div>
                                                                <template v-if="errors.vat_no">
                                                                    <ErrorMessage v-for="(errorMessage,index) in errors.vat_no" :key="index">{{ errorMessage }}</ErrorMessage>
                                                                </template>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label class=" mr-2" >
                                                                    {{ $t('general.Status') }}
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <b-form-group :class="{
                                                                'is-invalid':$v.create.is_active.$error || errors.is_active,
                                                                'is-valid':!$v.create.is_active.$invalid && !errors.is_active
                                                            }">
                                                                    <b-form-radio class="d-inline-block" v-model="$v.create.is_active.$model" name="some-radios" value="active">{{$t('general.Active')}}</b-form-radio>
                                                                    <b-form-radio class="d-inline-block m-1" v-model="$v.create.is_active.$model" name="some-radios" value="inactive">{{$t('general.Inactive')}}</b-form-radio>
                                                                </b-form-group>
                                                                <template v-if="errors.is_active">
                                                                    <ErrorMessage
                                                                        v-for="(errorMessage,index) in errors.is_active"
                                                                        :key="index">{{ errorMessage }}
                                                                    </ErrorMessage>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </b-tab>
                                        <b-tab :disabled="!company_id"  :title="$t('company.LogoFileUpload')">
                                            <div class="row">
                                                <input
                                                    accept="image/png, image/gif, image/jpeg, image/jpg"
                                                    type="file"
                                                    id="uploadImageCreate"
                                                    @change.prevent="onImageChanged"
                                                    class="input-file-upload position-absolute d-none"
                                                >
                                                <div class="col-md-8 my-1">
                                                    <!-- file upload -->
                                                    <div class="row align-content-between" style="width: 100%;height: 100%">
                                                        <div class="col-12">
                                                            <div class="d-flex flex-wrap">
                                                                <div
                                                                    :class="['dropzone-previews col-4 position-relative mb-2']"
                                                                    v-for="(photo,index) in images"
                                                                    :key="photo.id"
                                                                >
                                                                    <div :class="['card mb-0 shadow-none border',(images.length - 1) == index ? 'bg-primary': '']">
                                                                        <div class="p-2">
                                                                            <div class="row align-items-center">
                                                                                <div class="col-auto" @click="showPhoto = photo.webp">
                                                                                    <img
                                                                                        data-dz-thumbnail
                                                                                        :src="photo.webp"
                                                                                        class="avatar-sm rounded bg-light"
                                                                                        @error="src='./images/img-1.png'"
                                                                                    >
                                                                                </div>
                                                                                <div class="col pl-0">
                                                                                    <a href="javascript:void(0);"
                                                                                       :class="['font-weight-bold',(images.length - 1) == index ? 'text-white': 'text-muted']"
                                                                                       data-dz-name
                                                                                    >
                                                                                        {{ photo.name }}
                                                                                    </a>
                                                                                </div>
                                                                                <!-- Button -->
                                                                                <a
                                                                                    href="javascript:void(0);"
                                                                                    :class="['btn-danger dropzone-close',
                                                                                $i18n.locale == 'ar' ?'dropzone-close-rtl': ''
                                                                            ]"
                                                                                    data-dz-remove
                                                                                    @click.prevent="deleteImageCreate(photo.id,index)"
                                                                                >
                                                                                    <i class="fe-x"></i>
                                                                                </a>

                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="footer-image col-12">
                                                            <b-button
                                                                @click="changePhoto"
                                                                variant="success"
                                                                type="button" class="mx-1 font-weight-bold px-3"
                                                                v-if="!isLoader"
                                                            >
                                                                {{ $t('general.Add') }}
                                                            </b-button>
                                                            <b-button variant="success" class="mx-1" disabled v-else>
                                                                <b-spinner small></b-spinner>
                                                                <span class="sr-only">{{ $t('login.Loading') }}...</span>
                                                            </b-button>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="col-md-4">
                                                    <div class="show-dropzone">
                                                        <img
                                                            :src="showPhoto"
                                                            class="img-thumbnail"
                                                            @error="src='./images/img-1.png'"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        </b-tab>
                                    </b-tabs>
                                </div>
                            </form>
                        </b-modal>
                        <!--  /create   -->

                        <!-- start .table-responsive-->
                        <div class="table-responsive mb-3 custom-table-theme position-relative">

                            <!--       start loader       -->
                            <loader size="large" v-if="isLoader" />
                            <!--       end loader       -->

                            <table class="table table-borderless table-hover table-centered m-0">
                                <thead>
                                <tr>
                                    <th scope="col" style="width: 0;">
                                        <div class="form-check custom-control">
                                            <input
                                                class="form-check-input"
                                                type="checkbox" v-model="isCheckAll"
                                                style="width: 17px;height: 17px;"
                                            >
                                        </div>
                                    </th>
                                    <th v-if="setting.name">
                                        <div class="d-flex justify-content-center">
                                            <span>{{ $t('general.Name') }}</span>
                                            <div class="arrow-sort">
                                                <i class="fas fa-arrow-up" @click="companies.sort(sortString('name'))"></i>
                                                <i class="fas fa-arrow-down" @click="companies.sort(sortString('-name'))"></i>
                                            </div>
                                        </div>
                                    </th>
                                    <th v-if="setting.name_e">
                                        <div class="d-flex justify-content-center">
                                            <span>{{ $t('general.Name_en') }}</span>
                                            <div class="arrow-sort">
                                                <i class="fas fa-arrow-up" @click="companies.sort(sortString('name_e'))"></i>
                                                <i class="fas fa-arrow-down" @click="companies.sort(sortString('-name_e'))"></i>
                                            </div>
                                        </div>
                                    </th>
                                    <th v-if="setting.email">
                                        <div class="d-flex justify-content-center">
                                            <span>{{ $t('login.Emailaddress') }}</span>
                                            <div class="arrow-sort">
                                                <i class="fas fa-arrow-up" @click="companies.sort(sortString('email'))"></i>
                                                <i class="fas fa-arrow-down" @click="companies.sort(sortString('-email'))"></i>
                                            </div>
                                        </div>
                                    </th>
                                    <th v-if="setting.partner_id">{{ $t('partner.partner') }}</th>
                                    <th v-if="setting.phone">
                                        <div class="d-flex justify-content-center">
                                            <span>{{ $t('general.mobile_no') }}</span>
                                            <div class="arrow-sort">
                                                <i class="fas fa-arrow-up" @click="companies.sort(sortNumber('phone'))"></i>
                                                <i class="fas fa-arrow-down" @click="companies.sort(sortNumber('-phone'))"></i>
                                            </div>
                                        </div>
                                    </th>
                                    <th v-if="setting.vat_no">
                                        <div class="d-flex justify-content-center">
                                            <span>{{ $t('general.Valueaddedregistrationnumber') }}</span>
                                            <div class="arrow-sort">
                                                <i class="fas fa-arrow-up" @click="companies.sort(sortNumber('vat_no'))"></i>
                                                <i class="fas fa-arrow-down" @click="companies.sort(sortNumber('-vat_no'))"></i>
                                            </div>
                                        </div>
                                    </th>
                                    <th v-if="setting.cr">
                                        <div class="d-flex justify-content-center">
                                            <span>{{ $t('general.CommercialRecord') }}</span>
                                            <div class="arrow-sort">
                                                <i class="fas fa-arrow-up" @click="companies.sort(sortNumber('cr'))"></i>
                                                <i class="fas fa-arrow-down" @click="companies.sort(sortNumber('-cr'))"></i>
                                            </div>
                                        </div>
                                    </th>
                                    <th v-if="setting.tax_id">
                                        <div class="d-flex justify-content-center">
                                            <span>{{ $t('general.taxnumber') }}</span>
                                            <div class="arrow-sort">
                                                <i class="fas fa-arrow-up" @click="companies.sort(sortNumber('tax_id'))"></i>
                                                <i class="fas fa-arrow-down" @click="companies.sort(sortNumber('-tax_id'))"></i>
                                            </div>
                                        </div>
                                    </th>
                                    <th v-if="setting.address">
                                        <div class="d-flex justify-content-center">
                                            <span> {{ $t('general.address') }}</span>
                                            <div class="arrow-sort">
                                                <i class="fas fa-arrow-up" @click="companies.sort(sortString('address'))"></i>
                                                <i class="fas fa-arrow-down" @click="companies.sort(sortString('-address'))"></i>
                                            </div>
                                        </div>
                                    </th>
                                    <th v-if="setting.website">{{ $t('general.website') }}</th>
                                    <th v-if="setting.url">{{ $t('general.url') }}</th>
                                    <th v-if="setting.is_active">{{ $t('general.Status') }}</th>
                                    <th>{{ $t('general.Action') }}</th>
                                    <th><i class="fas fa-ellipsis-v"></i></th>
                                </tr>
                                </thead>
                                <tbody v-if="companies.length > 0">
                                <tr
                                    @click.capture="checkRow(data.id)"
                                    @dblclick.prevent="$bvModal.show(`modal-edit-${data.id}`)"
                                    v-for="(data,index) in companies"
                                    :key="data.id"
                                    class="body-tr-custom"
                                >
                                    <td>
                                        <div class="form-check custom-control" style="min-height: 1.9em;">
                                            <input
                                                style="width: 17px;height: 17px;"
                                                class="form-check-input"
                                                type="checkbox"
                                                :value="data.id"
                                                v-model="checkAll"
                                            >
                                        </div>
                                    </td>
                                    <td v-if="setting.name">
                                        {{ data.name }}
                                    </td>
                                    <td v-if="setting.name_e">
                                        {{ data.name_e }}
                                    </td>
                                    <td v-if="setting.email">
                                        {{ data.email }}
                                    </td>
                                    <td v-if="setting.partner_id">
                                        {{ $i18n.locale == 'ar'? data.partner.name : data.partner.name_e}}
                                    </td>
                                    <td v-if="setting.phone">
                                         {{ data.phone }}
                                    </td>
                                    <td v-if="setting.vat_no">
                                        {{ data.vat_no }}
                                    </td>
                                    <td v-if="setting.cr">
                                        {{ data.cr }}
                                    </td>
                                    <td v-if="setting.tax_id">
                                         {{ data.tax_id }}
                                    </td>
                                    <td v-if="setting.address">
                                        {{ data.address }}
                                    </td>
                                    <td v-if="setting.website">
                                        {{ data.website }}
                                    </td>
                                    <td v-if="setting.url">
                                        {{ data.url }}
                                    </td>
                                    <td v-if="setting.is_active">
                                        <span :class="[
                                            data.is_active == 'active' ?
                                            'bg-soft-success text-success':
                                            'bg-soft-danger  text-danger',
                                            'badge'
                                            ]"
                                        >
                                            {{ data.is_active == 'active' ? `${$t('general.Active')}`:`${$t('general.Inactive')}`}}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button
                                                type="button"
                                                class="btn btn-sm dropdown-toggle dropdown-coustom"
                                                data-toggle="dropdown"
                                                aria-expanded="false"
                                            >
                                                {{ $t('general.commands') }}
                                                <i class="fas fa-angle-down"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-custom">
                                                <a
                                                    class="dropdown-item"
                                                    href="#"
                                                    @click="$bvModal.show(`modal-edit-${data.id}`)"
                                                >
                                                    <div
                                                        class="d-flex justify-content-between align-items-center text-black"
                                                    >
                                                        <span>{{ $t('general.edit') }}</span>
                                                        <i class="mdi mdi-square-edit-outline text-info"></i>
                                                    </div>
                                                </a>
                                                <a
                                                    class="dropdown-item text-black"
                                                    href="#"
                                                    @click.prevent="deletecompany(data.id)"
                                                >
                                                    <div class="d-flex justify-content-between align-items-center text-black">
                                                        <span>{{ $t('general.delete') }}</span>
                                                        <i class="fas fa-times text-danger"></i>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>

                                        <!--  edit   -->
                                        <b-modal
                                            :id="`modal-edit-${data.id}`"
                                            :title="$t('company.editcompany')"
                                            title-class="font-18"
                                            :ref="`edit-${data.id}`"
                                            dialog-class="modal-full-width"
                                            body-class="paddingUnset"
                                            :hide-footer="true"
                                            @show="resetModalEdit(data.id)"
                                            @hidden="resetModalHiddenEdit(data.id)"
                                        >
                                            <form>

                                                <div class="card-body">
                                                    <div class="mt-1 d-flex justify-content-end">
                                                        <!-- Emulate built in modal footer ok and cancel button actions -->

                                                        <b-button
                                                            variant="success"
                                                            type="button" class="mx-1 font-weight-bold px-3"
                                                            v-if="!isLoader"
                                                            @click.prevent="editSubmit(data.id,index)"
                                                        >
                                                            {{ $t('general.Save') }}
                                                        </b-button>

                                                        <b-button variant="success" class="mx-1" disabled v-else>
                                                            <b-spinner small></b-spinner>
                                                            <span class="sr-only">{{ $t('login.Loading') }}...</span>
                                                        </b-button>


                                                        <b-button variant="danger" class="font-weight-bold" type="button" @click.prevent="resetModalHidden">
                                                            {{ $t('general.Cancel') }}
                                                        </b-button>
                                                    </div>
                                                </div>
                                                <b-tabs nav-class="nav-tabs nav-bordered">
                                                    <b-tab :title="$t('general.DataEntry')" active>
                                                        <div class="row justify-content-between">
                                                            <div class='col-7'>
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group position-relative">
                                                                            <label class="control-label">
                                                                                {{ $t('partner.partner') }}
                                                                                <span class="text-danger">*</span>
                                                                            </label>

                                                                            <multiselect
                                                                                v-model="edit.partner_id"
                                                                                :options="partners.map(type => type.id)"
                                                                                :custom-label="opt => $i18n.locale ? partners.find(x => x.id == opt).name : partners.find(x => x.id == opt).name_e">
                                                                            </multiselect>

                                                                            <div v-if="!$v.edit.partner_id.integer" class="invalid-feedback">{{ $t('general.fieldIsInteger') }}</div>
                                                                            <template v-if="errors.partner_id">
                                                                                <ErrorMessage v-for="(errorMessage,index) in errors.partner_id" :key="index">{{ errorMessage }}</ErrorMessage>
                                                                            </template>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6"></div>
                                                                    <div class="col-md-6 direction" dir="rtl">
                                                                        <div class="form-group">
                                                                            <label for="edit-1" class="control-label">
                                                                                {{ $t('general.Name') }}
                                                                                <span class="text-danger">*</span>
                                                                            </label>
                                                                            <input
                                                                                type="text"
                                                                                class="form-control arabicInput"
                                                                                v-model.trim="$v.edit.name.$model"
                                                                                :class="{
                                                                                    'is-invalid':$v.edit.name.$error || errors.name,
                                                                                    'is-valid':!$v.edit.name.$invalid && !errors.name
                                                                                }"
                                                                                id="edit-1"
                                                                            />
                                                                            <div v-if="!$v.edit.name.minLength" class="invalid-feedback">{{ $t('general.Itmustbeatleast') }} {{ $v.edit.name.$params.minLength.min }} {{ $t('general.letters') }}</div>
                                                                            <div v-if="!$v.edit.name.maxLength" class="invalid-feedback">{{ $t('general.Itmustbeatmost') }}  {{ $v.edit.name.$params.maxLength.max }} {{ $t('general.letters') }}</div>
                                                                            <div v-if="!$v.edit.name.alphaArabic" class="invalid-feedback">{{ $t('general.alphaArabic') }}</div>
                                                                            <template v-if="errors.name">
                                                                                <ErrorMessage v-for="(errorMessage,index) in errors.name" :key="index">{{ errorMessage }}</ErrorMessage>
                                                                            </template>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6 direction-ltr" dir="ltr">
                                                                        <div class="form-group">
                                                                            <label for="field-2" class="control-label">
                                                                                {{ $t('general.Name_en') }}
                                                                                <span class="text-danger">*</span>
                                                                            </label>
                                                                            <input
                                                                                type="text"
                                                                                class="form-control englishInput"
                                                                                v-model.trim="$v.edit.name_e.$model"
                                                                                :class="{
                                                                                    'is-invalid':$v.edit.name_e.$error || errors.name_e,
                                                                                    'is-valid':!$v.edit.name_e.$invalid && !errors.name_e
                                                                                }"
                                                                                id="edit-2"
                                                                            />
                                                                            <div v-if="!$v.edit.name_e.minLength" class="invalid-feedback">{{ $t('general.Itmustbeatleast') }} {{ $v.edit.name_e.$params.minLength.min }} {{ $t('general.letters') }}</div>
                                                                            <div v-if="!$v.edit.name_e.maxLength" class="invalid-feedback">{{ $t('general.Itmustbeatmost') }}  {{ $v.edit.name_e.$params.maxLength.max }} {{ $t('general.letters') }}</div>
                                                                            <div v-if="!$v.edit.name_e.alphaEnglish" class="invalid-feedback">{{ $t('general.alphaEnglish') }}</div>
                                                                            <template v-if="errors.name_e">
                                                                                <ErrorMessage v-for="(errorMessage,index) in errors.name_e" :key="index">{{ errorMessage }}</ErrorMessage>
                                                                            </template>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="edit-3" class="control-label">
                                                                                {{ $t('login.Emailaddress') }}
                                                                                <span class="text-danger">*</span>
                                                                            </label>
                                                                            <input
                                                                                type="email"
                                                                                class="form-control"
                                                                                v-model.trim="$v.edit.email.$model"
                                                                                :class="{
                                                                                    'is-invalid':$v.edit.email.$error || errors.email,
                                                                                    'is-valid':!$v.edit.email.$invalid && !errors.email
                                                                                }"
                                                                                id="edit-3"
                                                                            />
                                                                            <div v-if="!$v.edit.email.email" class="invalid-feedback">{{ $t('general.PleaseEnterValidEmail') }}</div>
                                                                            <div v-if="!$v.edit.email.minLength" class="invalid-feedback">{{ $t('general.Itmustbeatleast') }} {{ $v.edit.email.$params.minLength.min }} {{ $t('general.letters') }}</div>
                                                                            <div v-if="!$v.edit.email.maxLength" class="invalid-feedback">{{ $t('general.Itmustbeatmost') }}  {{ $v.edit.email.$params.maxLength.max }} {{ $t('general.letters') }}</div>
                                                                            <template v-if="errors.email">
                                                                                <ErrorMessage v-for="(errorMessage,index) in errors.email" :key="index">{{ errorMessage }}</ErrorMessage>
                                                                            </template>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label class="control-label">
                                                                                {{ $t('general.mobile_no') }}
                                                                                <span class="text-danger">*</span>
                                                                            </label>
                                                                            <VuePhoneNumberInput
                                                                                v-model="$v.edit.phone.$model"
                                                                                default-country-code="EG"
                                                                                valid-color="#28a745"
                                                                                error-color="#dc3545"
                                                                                :preferred-countries="['FR', 'EG', 'DE']"
                                                                                @update="updatePhoneEdit"
                                                                            />
                                                                            <template v-if="errors.phone">
                                                                                <ErrorMessage v-for="(errorMessage,index) in errors.phone" :key="index">{{ errorMessage }}</ErrorMessage>
                                                                            </template>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="field-12" class="control-label">
                                                                                {{ $t('general.address') }}
                                                                                <span class="text-danger">*</span>
                                                                            </label>
                                                                            <input
                                                                                type="text"
                                                                                class="form-control"
                                                                                v-model.number="$v.edit.address.$model"
                                                                                :class="{
                                                                                    'is-invalid':$v.edit.address.$error || errors.address,
                                                                                    'is-valid':!$v.edit.address.$invalid && !errors.address
                                                                                }"
                                                                                id="edit-12"
                                                                            />
                                                                            <div v-if="!$v.edit.address.minLength" class="invalid-feedback">{{ $t('general.Itmustbeatleast') }} {{ $v.edit.address.$params.minLength.min }} {{ $t('general.letters') }}</div>
                                                                            <div v-if="!$v.edit.address.maxLength" class="invalid-feedback">{{ $t('general.Itmustbeatmost') }}  {{ $v.edit.address.$params.maxLength.max }} {{ $t('general.letters') }}</div>
                                                                            <template v-if="errors.address">
                                                                                <ErrorMessage v-for="(errorMessage,index) in errors.address" :key="index">{{ errorMessage }}</ErrorMessage>
                                                                            </template>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="edit-11" class="control-label">
                                                                                {{ $t('general.website') }}
                                                                                <span class="text-danger">*</span>
                                                                            </label>
                                                                            <input
                                                                                type="url"
                                                                                class="form-control"
                                                                                v-model.number="$v.edit.website.$model"
                                                                                :class="{
                                                                                    'is-invalid':$v.edit.website.$error || errors.website,
                                                                                    'is-valid':!$v.edit.website.$invalid && !errors.website
                                                                                }"
                                                                                id="edit-11"
                                                                            />
                                                                            <div v-if="!$v.edit.website.minLength" class="invalid-feedback">{{ $t('general.Itmustbeatleast') }} {{ $v.edit.website.$params.minLength.min }} {{ $t('general.letters') }}</div>
                                                                            <div v-if="!$v.edit.website.maxLength" class="invalid-feedback">{{ $t('general.Itmustbeatmost') }}  {{ $v.edit.website.$params.maxLength.max }} {{ $t('general.letters') }}</div>
                                                                            <div v-if="!$v.edit.website.url" class="invalid-feedback">{{ $t('general.Itmustbeyourlink') }}</div>
                                                                            <template v-if="errors.website">
                                                                                <ErrorMessage v-for="(errorMessage,index) in errors.website" :key="index">{{ errorMessage }}</ErrorMessage>
                                                                            </template>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class='col-4'>
                                                                <div class="row">
                                                                    <div class="col-md-6" style="height: 67.6px;"></div>
                                                                    <div class="col-md-6" style="height: 67.6px;"></div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="edit-5" class="control-label">
                                                                                {{ $t('general.taxnumber') }}
                                                                                <span class="text-danger">*</span>
                                                                            </label>
                                                                            <input
                                                                                type="number"
                                                                                class="form-control"
                                                                                v-model.number="$v.edit.tax_id.$model"
                                                                                :class="{
                                                                                    'is-invalid':$v.edit.tax_id.$error || errors.tax_id,
                                                                                    'is-valid':!$v.edit.tax_id.$invalid && !errors.tax_id
                                                                                }"
                                                                                id="edit-5"
                                                                            />
                                                                            <div v-if="!$v.edit.tax_id.minLength" class="invalid-feedback">{{ $t('general.Itmustbeatleast') }} {{ $v.edit.tax_id.$params.minLength.min }} {{ $t('general.letters') }}</div>
                                                                            <div v-if="!$v.edit.tax_id.maxLength" class="invalid-feedback">{{ $t('general.Itmustbeatmost') }}  {{ $v.edit.tax_id.$params.maxLength.max }} {{ $t('general.letters') }}</div>
                                                                            <div v-if="!$v.edit.tax_id.integer" class="invalid-feedback">{{ $t('general.fieldIsInteger') }}</div>
                                                                            <template v-if="errors.tax_id">
                                                                                <ErrorMessage v-for="(errorMessage,index) in errors.tax_id" :key="index">{{ errorMessage }}</ErrorMessage>
                                                                            </template>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="field-8" class="control-label">
                                                                                {{ $t('general.CommercialRecord') }}
                                                                                <span class="text-danger">*</span>
                                                                            </label>
                                                                            <input
                                                                                type="text"
                                                                                class="form-control"
                                                                                v-model.trim="$v.edit.cr.$model"
                                                                                :class="{
                                                                                    'is-invalid':$v.edit.cr.$error || errors.cr,
                                                                                    'is-valid':!$v.edit.cr.$invalid && !errors.cr
                                                                                }"
                                                                                id="edit-8"
                                                                            />
                                                                            <div v-if="!$v.edit.cr.minLength" class="invalid-feedback">{{ $t('general.Itmustbeatleast') }} {{ $v.edit.cr.$params.minLength.min }} {{ $t('general.letters') }}</div>
                                                                            <div v-if="!$v.edit.cr.maxLength" class="invalid-feedback">{{ $t('general.Itmustbeatmost') }}  {{ $v.edit.cr.$params.maxLength.max }} {{ $t('general.letters') }}</div>
                                                                            <template v-if="errors.cr">
                                                                                <ErrorMessage v-for="(errorMessage,index) in errors.cr" :key="index">{{ errorMessage }}</ErrorMessage>
                                                                            </template>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="edit-10" class="control-label">
                                                                                {{ $t('general.companysystempath') }}
                                                                                <span class="text-danger">*</span>
                                                                            </label>
                                                                            <input
                                                                                type="url"
                                                                                class="form-control"
                                                                                v-model.number="$v.edit.url.$model"
                                                                                :class="{
                                                                                    'is-invalid':$v.edit.url.$error || errors.url,
                                                                                    'is-valid':!$v.edit.url.$invalid && !errors.url
                                                                                }"
                                                                                id="edit-10"
                                                                            />
                                                                            <div v-if="!$v.edit.url.minLength" class="invalid-feedback">{{ $t('general.Itmustbeatleast') }} {{ $v.edit.url.$params.minLength.min }} {{ $t('general.letters') }}</div>
                                                                            <div v-if="!$v.edit.url.maxLength" class="invalid-feedback">{{ $t('general.Itmustbeatmost') }}  {{ $v.edit.url.$params.maxLength.max }} {{ $t('general.letters') }}</div>
                                                                            <div v-if="!$v.edit.url.url" class="invalid-feedback">{{ $t('general.Itmustbeyourlink') }}</div>
                                                                            <template v-if="errors.url">
                                                                                <ErrorMessage v-for="(errorMessage,index) in errors.url" :key="index">{{ errorMessage }}</ErrorMessage>
                                                                            </template>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="field-6" class="control-label" style="font-size: 10px;">
                                                                                {{ $t('general.Valueaddedregistrationnumber') }}
                                                                                <span class="text-danger">*</span>
                                                                            </label>
                                                                            <input
                                                                                type="number"
                                                                                class="form-control"
                                                                                v-model.number="$v.edit.vat_no.$model"
                                                                                :class="{
                                                                                    'is-invalid':$v.edit.vat_no.$error || errors.vat_no,
                                                                                    'is-valid':!$v.edit.vat_no.$invalid && !errors.vat_no
                                                                                }"
                                                                                id="edit-6"
                                                                            />
                                                                            <div v-if="!$v.edit.vat_no.minLength" class="invalid-feedback">{{ $t('general.Itmustbeatleast') }} {{ $v.edit.vat_no.$params.minLength.min }} {{ $t('general.letters') }}</div>
                                                                            <div v-if="!$v.edit.vat_no.maxLength" class="invalid-feedback">{{ $t('general.Itmustbeatmost') }}  {{ $v.edit.vat_no.$params.maxLength.max }} {{ $t('general.letters') }}</div>
                                                                            <div v-if="!$v.edit.vat_no.integer" class="invalid-feedback">{{ $t('general.fieldIsInteger') }}</div>
                                                                            <template v-if="errors.vat_no">
                                                                                <ErrorMessage v-for="(errorMessage,index) in errors.vat_no" :key="index">{{ errorMessage }}</ErrorMessage>
                                                                            </template>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12">
                                                                        <div class="form-group">
                                                                            <label class=" mr-2" >
                                                                                {{ $t('general.Status') }}
                                                                                <span class="text-danger">*</span>
                                                                            </label>
                                                                            <b-form-group :class="{
                                                                                    'is-invalid':$v.edit.is_active.$error || errors.is_active,
                                                                                    'is-valid':!$v.edit.is_active.$invalid && !errors.is_active
                                                                                }">
                                                                                <b-form-radio class="d-inline-block" v-model="$v.edit.is_active.$model" name="some-radios" value="active">{{$t('general.Active')}}</b-form-radio>
                                                                                <b-form-radio class="d-inline-block m-1" v-model="$v.edit.is_active.$model" name="some-radios" value="inactive">{{$t('general.Inactive')}}</b-form-radio>
                                                                            </b-form-group>
                                                                            <template v-if="errors.is_active">
                                                                                <ErrorMessage
                                                                                    v-for="(errorMessage,index) in errors.is_active"
                                                                                    :key="index">{{ errorMessage }}
                                                                                </ErrorMessage>
                                                                            </template>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </b-tab>
                                                    <b-tab  :title="$t('company.LogoFileUpload')">
                                                        <div class="row">
                                                            <input
                                                                accept="image/png, image/gif, image/jpeg, image/jpg"
                                                                type="file"
                                                                id="uploadImageEdit"
                                                                @change.prevent="onImageChanged"
                                                                class="input-file-upload position-absolute d-none"
                                                            >
                                                            <div class="col-md-8 my-1">
                                                                <!-- file upload -->
                                                                <div class="row align-content-between" style="width: 100%;height: 100%">
                                                                    <div class="col-12">
                                                                        <div class="d-flex flex-wrap">
                                                                            <div
                                                                                :class="['dropzone-previews col-4 position-relative mb-2']"
                                                                                v-for="(photo,index) in images"
                                                                                :key="photo.id"
                                                                            >
                                                                                <div :class="['card mb-0 shadow-none border',(images.length - 1) == index ? 'bg-primary': '']">
                                                                                    <div class="p-2">
                                                                                        <div class="row align-items-center">
                                                                                            <div class="col-auto" @click="showPhoto = photo.webp">
                                                                                                <img
                                                                                                    data-dz-thumbnail
                                                                                                    :src="photo.webp"
                                                                                                    class="avatar-sm rounded bg-light"
                                                                                                    @error="src='./images/img-1.png'"
                                                                                                >
                                                                                            </div>
                                                                                            <div class="col pl-0">
                                                                                                <a href="javascript:void(0);"
                                                                                                   :class="['font-weight-bold',(images.length - 1) == index ? 'text-white': 'text-muted']"
                                                                                                   data-dz-name
                                                                                                >
                                                                                                    {{ photo.name }}
                                                                                                </a>
                                                                                            </div>
                                                                                            <!-- Button -->
                                                                                            <a
                                                                                                href="javascript:void(0);"
                                                                                                :class="['btn-danger dropzone-close',
                                                                                                    $i18n.locale == 'ar' ?'dropzone-close-rtl': ''
                                                                                                ]"
                                                                                                data-dz-remove
                                                                                                @click.prevent="deleteImageCreate(photo.id,index)"
                                                                                            >
                                                                                                <i class="fe-x"></i>
                                                                                            </a>

                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="footer-image col-12">
                                                                        <b-button
                                                                            @click="changePhotoEdit"
                                                                            variant="success"
                                                                            type="button" class="mx-1 font-weight-bold px-3"
                                                                            v-if="!isLoader"
                                                                        >
                                                                            {{ $t('general.Add') }}
                                                                        </b-button>
                                                                        <b-button variant="success" class="mx-1" disabled v-else>
                                                                            <b-spinner small></b-spinner>
                                                                            <span class="sr-only">{{ $t('login.Loading') }}...</span>
                                                                        </b-button>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="show-dropzone">
                                                                    <img
                                                                        :src="showPhoto"
                                                                        class="img-thumbnail"
                                                                        @error="src='./images/img-1.png'"
                                                                    />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </b-tab>
                                                </b-tabs>

                                            </form>
                                        </b-modal>
                                        <!--  /edit   -->
                                    </td>
                                    <td>
                                        <i class="fe-info" style="font-size: 22px;"></i>
                                    </td>
                                </tr>
                                </tbody>
                                <tbody v-else>
                                <tr>
                                    <th class="text-center" colspan="15">{{ $t('general.notDataFound') }}</th>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- end .table-responsive-->

                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style>
.dropdown-menu-custom-company.dropdown .dropdown-menu {
    padding: 5px 10px !important;
    overflow-y: scroll;
    height: 400px;
}
.modal-dialog .card {
    margin: 0 !important;
}
.modal-body.paddingUnset {
    padding: 0 !important;
}
.modal-dialog .card-body {
    padding: 1.5rem 1.5rem 0 1.5rem !important;
}
.nav-bordered{
    border: unset !important;
}
.nav {
    background-color: #dff0fe;
}
.tab-content {
    padding: 70px 60px 40px;
    min-height: 300px;
    background-color: #f5f5f5;
    position: relative;
}
.nav-tabs .nav-link {
    border: 1px solid #b7b7b7 !important;
    background-color: #d7e5f2;
    border-bottom: 0 !important;
    margin-bottom: 1px;
}

.nav-tabs .nav-link.active, .nav-tabs .nav-item.show .nav-link {
    color: #000;
    background-color: hsl(0deg 0% 96%);
    border-bottom: 0 !important;
}

.img-thumbnail {
    max-height: 400px !important;
}
</style>




