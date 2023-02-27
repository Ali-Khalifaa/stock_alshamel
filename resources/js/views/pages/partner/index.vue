<script>
import Layout from "../../layouts/main";
import PageHeader from "../../../components/Page-header";
import ErrorMessage from "../../../components/widgets/errorMessage";
import adminApi from "../../../api/adminAxios";
import Switches from "vue-switches";
import { required, minLength, maxLength ,integer,email, sameAs} from "vuelidate/lib/validators";
import Swal from "sweetalert2";
import loader from "../../../components/loader";
import {dynamicSortString,dynamicSortNumber} from "../../../helper/tableSort";
import {formatDateOnly} from "../../../helper/startDate";


/**
 * Advanced Table component
 */
export default {
    page: {
        title: "Partner",
        meta: [{ name: "description", content: 'Partner' }],
    },
    components: {
        Layout,
        PageHeader,
        Switches,
        loader,
        ErrorMessage
    },
    data() {
        return {
            per_page: 50,
            search: '',
            debounce: {},
            partnersPagination: {},
            partners: [],
            enabled3: false,
            isLoader: false,
            create: {
                name: '',
                name_e: '',
                email: '',
                password:'',
                repeatPassword:'',
                mobile_no:'',
                phone_code:'',
                country_code:'',
                is_active: 'active'
            },
            edit: {
                name: '',
                name_e: '',
                email: '',
                mobile_no:'',
                phone_code:'',
                country_code:'',
                is_active: 'active'
            },
            setting: {
                name: true,
                name_e: true,
                is_active: true,
                mobile_no:true,
                email: true
            },
            filterSetting: ['name', 'name_e','email','mobile_no'],
            errors: {},
            isEye: false,
            isVaildPhone: false,
            isEyeEdit: false,
            isCheckAll: false,
            checkAll: [],
            is_disabled: false,
            current_page: 1,
            Tooltip: ''
        }
    },
    validations: {
        create: {
            name: {required,minLength: minLength(3),maxLength: maxLength(100)},
            name_e: {required,minLength: minLength(3),maxLength: maxLength(100)},
            email: {required,email,minLength: minLength(3),maxLength: maxLength(100)},
            password: {required,minLength: minLength(8),maxLength: maxLength(16)},
            repeatPassword: {required,sameAs: sameAs('password')},
            mobile_no: {required,integer},
            is_active: {required}
        },
        edit: {
            name: {required,minLength: minLength(3),maxLength: maxLength(100)},
            name_e: {required,minLength: minLength(3),maxLength: maxLength(100)},
            email: {required,email,minLength: minLength(3),maxLength: maxLength(100)},
            mobile_no: {required,integer},
            is_active: {required}
        },
    },
    watch: {
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
                this.partners.forEach(el => {
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
         *  get Data Partner
         */
        getData(page = 1){

            this.isLoader = true;
            let filter = '';
            for (let i = 0; i > this.filterSetting.length; ++i) {
                filter += `columns[${i}]=${this.filterSetting[i]}&`;
            }

            adminApi.get(`/partners?page=${page}&per_page=${this.per_page}&search=${this.search}&${filter}`)
                .then((res) => {
                    let l = res.data;
                    this.partners = l.data;
                    this.partnersPagination = l.pagination;
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
            if(this.current_page <= this.partnersPagination.last_page && this.current_page != this.partnersPagination.current_page && this.current_page){
                this.isLoader = true;
                let filter = '';
                for (let i = 0; i > this.filterSetting.length; ++i) {
                    filter += `columns[${i}]=${this.filterSetting[i]}&`;
                }

                adminApi.get(`/partners?page=${this.current_page}&per_page=${this.per_page}&search=${this.search}&${filter}`)
                    .then((res) => {
                        let l = res.data;
                        this.partners = l.data;
                        this.current_page = l.pagination.current_page;
                        this.partnersPagination = l.pagination;
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
         *  delete Partner
         */
        deletePartner(id,index) {
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

                    adminApi.delete(`/partners/${id}`)
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
                password:'',
                repeatPassword:'',
                mobile_no:'',
                phone_code:'',
                country_code:'',
                is_active: 'active'
            };
            this.$nextTick(() => { this.$v.$reset() });
            this.$bvModal.hide(`create`);
            this.errors = {};
            this.isVaildPhone = false;
        },
        /**
         *  hidden Modal (create)
         */
        resetModal(){
            this.create =  {
                name: '',
                name_e: '',
                email: '',
                password:'',
                repeatPassword:'',
                mobile_no:'',
                phone_code:'',
                country_code:'',
                is_active: 'active'
            };
            this.$nextTick(() => { this.$v.$reset() });
            this.isVaildPhone = false;
            this.errors = {};
            this.is_disabled = false;
        },
        /**
         *  create Partner
         */
        resetForm(){
            this.create =  {name: '', name_e: '', email: '', password:'', repeatPassword:'', mobile_no:'', is_active: 'active'};
            this.$nextTick(() => { this.$v.$reset() });
            this.is_disabled = false;
        },
        AddSubmit(){
            this.$v.create.$touch();

            if (this.$v.create.$invalid && !this.isVaildPhone) {
                return;
            } else {
                this.isLoader = true;
                this.errors = {};
                if(!this.create.name_e){ this.create.name_e = this.create.name}
                if(!this.create.name){ this.create.name = this.create.name_e}

                adminApi.post(`/partners`,this.create)
                    .then((res) => {
                        this.getData();
                        this.is_disabled = true;
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
         *  edit Partner
         */
        editSubmit(id){
            this.$v.edit.$touch();

            if (this.$v.edit.$invalid && !this.isVaildPhone) {
                return;
            } else {
                this.isLoader = true;
                this.errors = {};
                adminApi.put(`/partners/${id}`,this.edit)
                    .then((res) => {
                        this.$bvModal.hide(`modal-edit-${id}`);
                        this.getData();
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
         *   show Modal (edit)
         */
        resetModalEdit(id){
            let Partner = this.partners.find(e => id == e.id );
            this.edit.name = Partner.name;
            this.edit.name_e = Partner.name_e;
            this.edit.is_active = Partner.is_active;
            this.edit.email = Partner.email;
            this.edit.mobile_no = Partner.mobile_no;
            this.isVaildPhone = false;
        },
        /**
         *  hidden Modal (edit)
         */
        resetModalHiddenEdit(){
            this.edit = {
                name: '',
                name_e: '',
                email: '',
                mobile_no:'',
                phone_code:'',
                country_code:'',
                is_active: 'active'
            };
            this.errors = {};
        },
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
        sortString(value){
            return dynamicSortString(value);
        },
        sortNumber(){
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
        formatDate(value){return formatDateOnly(value);},
        log(id){
            this.Tooltip = '';
            adminApi.get(`/partners/logs/${id}`)
                .then((res) => {
                    let l = res.data.data;
                    l.forEach((e) => {
                        this.Tooltip += `Created By: ${e.causer_type}; Event: ${e.event}; Description: ${e.description} ;Created At: ${this.formatDate(e.created_at)} \n`
                    });
                })
                .catch((err) => {
                    Swal.fire({
                        icon: 'error',
                        title: `${this.$t('general.Error')}`,
                        text: `${this.$t('general.Thereisanerrorinthesystem')}`,
                    });
                }).finally(() => {
                this.isLoader = false;
            });
        }
    }
};
</script>

<template>
    <Layout>
        <PageHeader />
        <div class="row no-gutters">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row justify-content-between align-items-center mb-2">
                            <h4 class="header-title"> {{ $t('partner.partnersTable') }}</h4>
                            <div class="col-xs-10 col-md-9 col-lg-7" style="font-weight: 500">

                                <div class="d-inline-block" style="width: 22.2%;">
                                    <!-- Basic dropdown -->
                                    <b-dropdown variant="primary" :text="$t('general.searchSetting')" ref="dropdown" class="btn-block setting-search">
                                        <b-form-checkbox v-model="filterSetting" value="name" class="mb-1">{{ $t('general.Name') }}</b-form-checkbox>
                                        <b-form-checkbox v-model="filterSetting" value="name_e" class="mb-1">{{ $t('general.Name_en') }}</b-form-checkbox>
                                        <b-form-checkbox v-model="filterSetting" value="email" class="mb-1">{{ $t('login.Emailaddress') }}</b-form-checkbox>
                                        <b-form-checkbox v-model="filterSetting" value="mobile_no" class="mb-1">{{ $t('general.mobile_no') }}</b-form-checkbox>
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
                                        @click.prevent="deletePartner(checkAll)"
                                    >
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <!-- end mult delete  -->
                                    <!--  start one delete  -->
                                    <button
                                        class="custom-btn-dowonload"
                                        v-if="checkAll.length == 1"
                                        @click.prevent="deletePartner(checkAll)"
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
                                    <!-- Basic dropdown -->
                                    <b-dropdown variant="primary"
                                                :html="`${$t('general.setting')} <i class='fe-settings'></i>`"
                                                ref="dropdown" class="dropdown-custom-ali">
                                        <b-form-checkbox v-model="setting.name" class="mb-1">{{
                                                $t('general.Name')
                                            }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="setting.name_e" class="mb-1">
                                            {{ $t('general.Name_en') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="setting.mobile_no" class="mb-1">
                                            {{ $t('general.mobile_no') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="setting.email" class="mb-1">
                                            {{ $t('login.Emailaddress') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="setting.is_active" class="mb-1">
                                            {{ $t('general.Status') }}
                                        </b-form-checkbox>
                                        <div class="d-flex justify-content-end">
                                            <a href="javascript:void(0)" class="btn btn-primary btn-sm">Apply</a>
                                        </div>
                                    </b-dropdown>
                                    <!-- Basic dropdown -->
                                    <!-- start Pagination -->
                                    <div class="d-inline-flex align-items-center pagination-custom">
                                        <div class="d-inline-block" style="font-size:15px;">
                                            {{ partnersPagination.from }}-{{ partnersPagination.to }} / {{ partnersPagination.total }}
                                        </div>
                                        <div class="d-inline-block">
                                            <a
                                                href="javascript:void(0)"
                                                :style="{'pointer-events':partnersPagination.current_page == 1 ? 'none': ''}"
                                                @click.prevent="getData(partnersPagination.current_page - 1)"
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
                                                :style="{'pointer-events':partnersPagination.last_page == partnersPagination.current_page ? 'none': ''}"
                                                @click.prevent="getData(partnersPagination.current_page + 1)"
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
                            :title="$t('partner.addpartner')"
                            title-class="font-18"
                            size="lg"
                            body-class="p-4"
                            :hide-footer="true"
                            @show="resetModal"
                            @hidden="resetModalHidden"
                        >
                            <form>
                                <div class="mb-3 d-flex justify-content-end">
                                    <b-button
                                        variant="success"
                                        :disabled="!is_disabled"
                                        @click.prevent="resetForm"
                                        type="button" :class="['font-weight-bold px-2',!is_disabled? '':'mx-2']"
                                    >
                                        {{ $t('general.AddNewRecord') }}
                                    </b-button>
                                    <!-- Emulate built in modal footer ok and cancel button actions -->
                                    <template v-if="!is_disabled">
                                        <b-button  variant="success" type="button" class="mx-1" v-if="!isLoader" @click.prevent="AddSubmit">
                                            {{ $t('general.Add') }}
                                        </b-button>

                                        <b-button variant="success" class="mx-1" disabled v-else>
                                            <b-spinner small></b-spinner>
                                            <span class="sr-only">{{ $t('login.Loading') }}...</span>
                                        </b-button>
                                    </template>

                                    <b-button variant="danger" type="button" @click.prevent="$bvModal.hide(`create`)">
                                        {{ $t('general.Cancel') }}
                                    </b-button>
                                </div>
                                <div class="row">
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
                                            <div v-if="!$v.create.name.alphaArabic" class="invalid-feedback">{{ $t('general.alphaArabic') }}</div>
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
                                            <div v-if="!$v.create.name_e.alphaEnglish" class="invalid-feedback">{{ $t('general.alphaEnglish') }}</div>
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
                                                v-model="$v.create.mobile_no.$model"
                                                default-country-code="EG"
                                                valid-color="#28a745"
                                                error-color="#dc3545"
                                                :preferred-countries="['FR', 'EG', 'DE']"
                                                @update="updatePhone"
                                            />
                                            <template v-if="errors.mobile_no">
                                                <ErrorMessage v-for="(errorMessage,index) in errors.mobile_no" :key="index">{{ errorMessage }}</ErrorMessage>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <label for="field-5" class="control-label">
                                                    {{ $t('login.Password') }}
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <i
                                                    :class="['fas custom-eye',!isEye ?'fa-eye-slash': 'fas fa-eye active']"
                                                   @click="isEye = !isEye"
                                                ></i>
                                            </div>
                                            <input
                                                :type="!isEye?'password':'text'"
                                                class="form-control"
                                                v-model.trim="$v.create.password.$model"
                                                :class="{
                                                'is-invalid':$v.create.password.$error || errors.password,
                                                'is-valid':!$v.create.password.$invalid && !errors.password
                                            }"
                                                id="field-5"
                                            />
                                            <div v-if="!$v.create.password.minLength" class="invalid-feedback">{{ $t('general.Itmustbeatleast') }} {{ $v.create.password.$params.minLength.min }} {{ $t('general.letters') }}</div>
                                            <div v-if="!$v.create.password.maxLength" class="invalid-feedback">{{ $t('general.Itmustbeatmost') }}  {{ $v.create.password.$params.maxLength.max }} {{ $t('general.letters') }}</div>
                                            <template v-if="errors.password">
                                                <ErrorMessage v-for="(errorMessage,index) in errors.password" :key="index">{{ errorMessage }}</ErrorMessage>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="field-6" class="control-label">
                                                {{ $t('general.repeatPassword') }}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input
                                                type="password"
                                                class="form-control"
                                                v-model.trim="$v.create.repeatPassword.$model"
                                                :class="{
                                                'is-invalid':$v.create.repeatPassword.$error || errors.repeatPassword,
                                                'is-valid':!$v.create.repeatPassword.$invalid && !errors.repeatPassword
                                            }"
                                                 id="field-6"
                                            />
                                            <div v-if="!$v.create.repeatPassword.sameAs" class="invalid-feedback">{{ $t('general.samaAs') }} </div>
                                            <template v-if="errors.repeatPassword">
                                                <ErrorMessage v-for="(errorMessage,index) in errors.repeatPassword" :key="index">{{ errorMessage }}</ErrorMessage>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class=" mr-2 mb-2" >
                                                {{ $t('general.Status') }}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <b-form-group
                                                :class="{
                                                    'is-invalid':$v.edit.is_active.$error || errors.is_active,
                                                    'is-valid':!$v.edit.is_active.$invalid && !errors.is_active
                                                }"
                                            >
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
                            </form>
                        </b-modal>
                        <!--  /create   -->

                        <!-- start .table-responsive-->
                        <div class="table-responsive mb-3 custom-table-theme position-relative">

                            <!--       start loader       -->
                            <loader size="large" v-if="isLoader" />
                            <!--       end loader       -->

                            <table class="table table-borderless table-centered m-0">
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
                                                <i class="fas fa-arrow-up" @click="partners.sort(sortString('name'))"></i>
                                                <i class="fas fa-arrow-down" @click="partners.sort(sortString('-name'))"></i>
                                            </div>
                                        </div>
                                    </th>
                                    <th v-if="setting.name_e">
                                        <div class="d-flex justify-content-center">
                                            <span>{{ $t('general.Name_en') }}</span>
                                            <div class="arrow-sort">
                                                <i class="fas fa-arrow-up" @click="partners.sort(sortString('name_e'))"></i>
                                                <i class="fas fa-arrow-down" @click="partners.sort(sortString('-name_e'))"></i>
                                            </div>
                                        </div>
                                    </th>
                                    <th  v-if="setting.email">
                                        <div class="d-flex justify-content-center">
                                            <span>{{ $t('login.Emailaddress') }}</span>
                                            <div class="arrow-sort">
                                                <i class="fas fa-arrow-up" @click="partners.sort(sortString('email'))"></i>
                                                <i class="fas fa-arrow-down" @click="partners.sort(sortString('-email'))"></i>
                                            </div>
                                        </div>
                                    </th>
                                    <th  v-if="setting.mobile_no">
                                        <div class="d-flex justify-content-center">
                                            <span> {{ $t('general.mobile_no') }}</span>
                                            <div class="arrow-sort">
                                                <i class="fas fa-arrow-up" @click="partners.sort(sortNumber('mobile_no'))"></i>
                                                <i class="fas fa-arrow-down" @click="partners.sort(sortNumber('-mobile_no'))"></i>
                                            </div>
                                        </div>
                                    </th>
                                    <th  v-if="setting.is_active">
                                        {{ $t('general.Status') }}
                                    </th>
                                    <th>{{ $t('general.Action') }}</th>
                                    <th><i class="fas fa-ellipsis-v"></i></th>
                                </tr>
                                </thead>
                                <tbody v-if="partners.length > 0">
                                <tr
                                    v-for="(data,index) in partners"
                                    :key="data.id" class="body-tr-custom"
                                    @click.capture="checkRow(data.id)"
                                    @dblclick.prevent="$bvModal.show(`modal-edit-${data.id}`)"
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
                                    <td  v-if="setting.name">
                                        <h5 class="m-0 font-weight-normal">{{ data.name }}</h5>
                                    </td>
                                    <td  v-if="setting.name_e">{{ data.name_e }}</td>
                                    <td  v-if="setting.email">{{ data.email }}</td>
                                    <td  v-if="setting.mobile_no">{{ data.mobile_no }}</td>
                                    <td  v-if="setting.is_active">
                                        <span :class="[
                                            data.is_active == 'active'?
                                            'bg-soft-success text-success':
                                            'bg-soft-danger  text-danger',
                                            'badge'
                                            ]"
                                        >
                                            {{ data.is_active == 'active'? `${$t('general.Active')}`:`${$t('general.Inactive')}`}}
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
                                                    @click.prevent="deletePartner(data.id)"
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
                                            :title="$t('partner.editpartner')"
                                            title-class="font-18"
                                            body-class="p-4"
                                            size="lg"
                                            :ref="`edit-${data.id}`"
                                            :hide-footer="true"
                                            @show="resetModalEdit(data.id)"
                                            @hidden="resetModalHiddenEdit(data.id)"
                                        >
                                            <form>
                                                <div class="mb-3 d-flex justify-content-end">
                                                    <!-- Emulate built in modal footer ok and cancel button actions -->

                                                    <b-button  variant="success" type="button" class="mx-1" v-if="!isLoader" @click.prevent="editSubmit(data.id)">
                                                        {{ $t('general.Add') }}
                                                    </b-button>

                                                    <b-button variant="success" class="mx-1" disabled v-else>
                                                        <b-spinner small></b-spinner>
                                                        <span class="sr-only">{{ $t('login.Loading') }}...</span>
                                                    </b-button>

                                                    <b-button variant="danger" type="button" @click.prevent="$bvModal.hide(`modal-edit-${data.id}`)">
                                                        {{ $t('general.Cancel') }}
                                                    </b-button>
                                                </div>
                                                <div class="row">
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
                                                            <label for="edit-2" class="control-label">
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
                                                            <label  class="control-label">
                                                                {{ $t('general.mobile_no') }}
                                                                <span class="text-danger">*</span>
                                                            </label>
                                                            <VuePhoneNumberInput
                                                                v-model="$v.edit.mobile_no.$model"
                                                                default-country-code="EG"
                                                                valid-color="#28a745"
                                                                error-color="#dc3545"
                                                                :preferred-countries="['FR', 'EG', 'DE']"
                                                                @update="updatePhoneEdit"
                                                            />
                                                             <template v-if="errors.mobile_no">
                                                                <ErrorMessage v-for="(errorMessage,index) in errors.mobile_no" :key="index">{{ errorMessage }}</ErrorMessage>
                                                            </template>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class=" mr-2 mb-2" >
                                                                {{ $t('general.Status') }}
                                                                <span class="text-danger">*</span>
                                                            </label>
                                                            <b-form-group
                                                                :class="{
                                                                'is-invalid':$v.edit.is_active.$error || errors.is_active,
                                                                'is-valid':!$v.edit.is_active.$invalid && !errors.is_active
                                                                }"
                                                            >
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
                                            </form>
                                        </b-modal>
                                        <!--  /edit   -->
                                    </td>
                                    <td @mouseup="log(data.id)">
                                        <button
                                            type="button"
                                            class="btn"
                                            data-toggle="tooltip"
                                            :data-placement="$i18n.locale == 'en' ? 'left' : 'right'"
                                            :title="Tooltip"
                                        >
                                            <i class="fe-info" style="font-size: 22px;"></i>
                                        </button>
                                    </td>
                                </tr>
                                </tbody>
                                <tbody v-else>
                                <tr>
                                    <th class="text-center" colspan="7">{{ $t('general.notDataFound') }}</th>
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
