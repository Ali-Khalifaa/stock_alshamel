<script>
import Layout from "../../layouts/main";
import PageHeader from "../../../components/Page-header";
import ErrorMessage from "../../../components/widgets/errorMessage";
import adminApi from "../../../api/adminAxios";
import Switches from "vue-switches";
import { required, minLength, maxLength ,integer,email, sameAs} from "vuelidate/lib/validators";
import Swal from "sweetalert2";
import loader from "../../../components/loader";
import {dynamicSortString,dynamicSortNumber, dynamicSortDate} from "../../../helper/tableSort";
import {formatDateTime,formatDateOnly} from "../../../helper/startDate";
import DatePicker from "vue2-datepicker";
import Multiselect from "vue-multiselect";

/**
 * Advanced Table component
 */
export default {
    page: {
        title: "Company Module",
        meta: [{ name: "description", content: 'Company Module' }],
    },
    components: {
        Layout,
        PageHeader,
        Switches,
        loader,
        ErrorMessage,
        DatePicker,
        Multiselect
    },
    data() {
        return {
            per_page: 50,
            search: '',
            debounce: {},
            companyModulesPagination: {},
            companyModules: [],
            enabled3: false,
            isLoader: false,
            companies: [],
            modules: [],
            create: {
                company_id: null,
                module_id: null,
                allowed_users_no: '',
                start_date:  null,
                end_date: null,
                custom_date_start: new Date(),
                custom_date_end: null
            },
            edit: {
                company_id: null,
                module_id: null,
                allowed_users_no: '',
                start_date:  null,
                end_date: null,
                custom_date_start: null,
                custom_date_end: null
            },
            setting: {
                company_id: true,
                module_id: true,
                allowed_users_no: true,
                start_date:true,
                end_date: true
            },
            filterSetting: ['company_id', 'module_id','allowed_users_no','start_date','end_date'],
            errors: {},
            isEye: false,
            isEyeEdit: false,
            isCheckAll: false,
            checkAll: [],
            is_disabled: false,
            current_page: 1
        }
    },
    validations: {
        create: {
            company_id: {required,integer},
            module_id: {required,integer},
            allowed_users_no: {required,integer},
            start_date: {required},
            end_date: {},
        },
        edit: {
            company_id: {required,integer},
            module_id: {required,integer},
            allowed_users_no: {required,integer},
            start_date: {required},
            end_date: {},
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
                this.companyModules.forEach(el => {
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
         *  get Data companyModule
         */
        getData(page = 1){

            this.isLoader = true;
            let filter = '';
            for (let i = 0; i > this.filterSetting.length; ++i) {
                filter += `columns[${i}]=${this.filterSetting[i]}&`;
            }

            adminApi.get(`/company-modules?page=${page}&per_page=${this.per_page}&search=${this.search}&${filter}`)
                .then((res) => {
                    let l = res.data;
                    this.companyModules = l.data;
                    this.companyModulesPagination = l.pagination;
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
            if(this.current_page <= this.companyModulesPagination.last_page && this.current_page != this.companyModulesPagination.current_page && this.current_page){
                this.isLoader = true;
                let filter = '';
                for (let i = 0; i > this.filterSetting.length; ++i) {
                    filter += `columns[${i}]=${this.filterSetting[i]}&`;
                }

                adminApi.get(`/company-modules?page=${this.current_page}&per_page=${this.per_page}&search=${this.search}&${filter}`)
                    .then((res) => {
                        let l = res.data;
                        this.companyModules = l.data;
                        this.current_page = l.pagination.current_page;
                        this.companyModulesPagination = l.pagination;
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
         *  delete companyModule
         */
        deleteCompanyModule(id,index) {
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

                    adminApi.delete(`/company-modules/${id}`)
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
                company_id: null,
                module_id: null,
                allowed_users_no: '',
                start_date:  null,
                end_date: null,
                custom_date_start: new Date(),
                custom_date_end: null
            };
            this.$nextTick(() => { this.$v.$reset() });
            this.$bvModal.hide(`create`);
            this.errors = {};
            this.companies = [];
            this.modules = [];
        },
        /**
         *  hidden Modal (create)
         */
        async resetModal(){
            await this.getCompany();
            await this.getModule();
            this.create =  {
                company_id: null,
                module_id: null,
                allowed_users_no: '',
                start_date:  formatDateTime(this.create.custom_date_start),
                end_date: null,
                custom_date_start: new Date(),
                custom_date_end: null
            };
            this.$nextTick(() => { this.$v.$reset() });
            this.errors = {};
            this.is_disabled = false;
        },
        /**
         *  create companyModule
         */
        async resetForm(){
            await this.getCompany();
            await this.getModule();
            this.create =  {
                company_id: '',
                module_id: '',
                allowed_users_no: '',
                start_date:  formatDateTime(new Date()),
                end_date: null,
                custom_date_start: new Date(),
                custom_date_end: null
            };
            this.$nextTick(() => { this.$v.$reset() });
            this.is_disabled = false;
        },
        AddSubmit(){
            this.$v.create.$touch();

            if (this.$v.create.$invalid) {
                return;
            } else {
                this.isLoader = true;
                this.errors = {};

                adminApi.post(`/company-modules`,this.create)
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
         *  edit companyModule
         */
        editSubmit(id){
            this.$v.edit.$touch();

            if (this.$v.edit.$invalid) {
                return;
            } else {
                this.isLoader = true;
                this.errors = {};
                adminApi.put(`/company-modules/${id}`,this.edit)
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
        async resetModalEdit(id){
            let companyModule = this.companyModules.find(e => id == e.id );
            await this.getCompany();
            await this.getModule();
            this.edit.company_id = companyModule.company.id;
            this.edit.module_id = companyModule.module.id;
            this.edit.allowed_users_no = companyModule.allowed_users_no;
            this.edit.custom_date_start = new Date(companyModule.start_date);
            this.edit.custom_date_end = new Date(companyModule.end_date);
            this.edit.start_date = formatDateTime(companyModule.start_date);
            this.edit.end_date = formatDateTime(companyModule.end_date);
        },
        /**
         *  hidden Modal (edit)
         */
        resetModalHiddenEdit(){
            this.edit = {
                company_id: '',
                module_id: '',
                allowed_users_no: '',
                start_date:  null,
                end_date: null,
                custom_date_start: null,
                custom_date_end: null
            };
            this.errors = {};
            this.companies = [];
            this.modules = [];
        },
        /**
         *  start  dynamicSortString
         */
        sortString(value){
            return dynamicSortString(value);
        },
        sortNumber(value){
            return dynamicSortNumber(value);
        },
        SortDate(value){return dynamicSortDate(value);},
        checkRow(id){
            if(!this.checkAll.includes(id)) {
                this.checkAll.push(id);
            }else {
                let index = this.checkAll.indexOf(id);
                this.checkAll.splice(index,1);
            }
        },
        start_date(e){
            if (e){
                this.create.start_date = formatDateTime(e);
                this.edit.start_date = formatDateTime(e);
            }else{
                this.create.start_date = null;
                this.edit.start_date = null;
            }
        },
        end_date(e){
            if (e){
                this.create.end_date = formatDateTime(e);
                this.edit.end_date = formatDateTime(e);
            }else{
                this.create.end_date = null;
                this.edit.end_date = null;
            }
        },
        formatDate(value){return formatDateOnly(value);},
        async getCompany(){
            this.isLoader = true;

            await adminApi.get(`/companies`)
                .then((res) => {
                    let l = res.data;
                    this.companies = l.data;
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
        async getModule(){
            this.isLoader = true;

            await adminApi.get(`/modules?is_active=active`)
                .then((res) => {
                    let l = res.data;
                    this.modules = l.data;
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
                            <h4 class="header-title"> {{ $t('companyModule.table') }}</h4>
                            <div class="col-xs-10 col-md-9 col-lg-7" style="font-weight: 500">

                                <div class="d-inline-block" style="width: 22.2%;">
                                    <!-- Basic dropdown -->
                                    <b-dropdown variant="primary" :text="$t('general.searchSetting')" ref="dropdown" class="btn-block setting-search">
                                        <b-form-checkbox v-model="filterSetting" value="company_id" class="mb-1">{{ $t('company.company') }}</b-form-checkbox>
                                        <b-form-checkbox v-model="filterSetting" value="module_id" class="mb-1">{{ $t('module.module') }}</b-form-checkbox>
                                        <b-form-checkbox v-model="filterSetting" value="allowed_users_no" class="mb-1">{{ $t('general.allowed_users_no') }}</b-form-checkbox>
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
                                        @click.prevent="deleteCompanyModule(checkAll)"
                                    >
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <!-- end mult delete  -->
                                    <!--  start one delete  -->
                                    <button
                                        class="custom-btn-dowonload"
                                        v-if="checkAll.length == 1"
                                        @click.prevent="deleteCompanyModule(checkAll)"
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
                                        <b-form-checkbox v-model="setting.company_id" class="mb-1">{{
                                                $t('company.company')
                                            }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="setting.module_id" class="mb-1">
                                            {{ $t('module.module') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="setting.allowed_users_no" class="mb-1">
                                            {{ $t('general.allowed_users_no') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="setting.start_date" class="mb-1">
                                            {{ $t('general.start_date') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="setting.end_date" class="mb-1">
                                            {{ $t('general.end_date') }}
                                        </b-form-checkbox>
                                        <div class="d-flex justify-content-end">
                                            <a href="javascript:void(0)" class="btn btn-primary btn-sm">Apply</a>
                                        </div>
                                    </b-dropdown>
                                    <!-- Basic dropdown -->
                                    <!-- start Pagination -->
                                    <div class="d-inline-flex align-items-center pagination-custom">
                                        <div class="d-inline-block" style="font-size:15px;">
                                            {{ companyModulesPagination.from }}-{{ companyModulesPagination.to }} / {{ companyModulesPagination.total }}
                                        </div>
                                        <div class="d-inline-block">
                                            <a
                                                href="javascript:void(0)"
                                                :style="{'pointer-events':companyModulesPagination.current_page == 1 ? 'none': ''}"
                                                @click.prevent="getData(companyModulesPagination.current_page - 1)"
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
                                                :style="{'pointer-events':companyModulesPagination.last_page == companyModulesPagination.current_page ? 'none': ''}"
                                                @click.prevent="getData(companyModulesPagination.current_page + 1)"
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
                            :title="$t('companyModule.add')"
                            title-class="font-18"
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
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label  class="control-label">
                                                {{ $t('company.company') }}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <multiselect
                                                v-model="$v.create.company_id.$model"
                                                :options="companies.map(type => type.id)"
                                                :custom-label="opt => $i18n.locale == 'ar' ? companies.find(x => x.id == opt).name : companies.find(x => x.id == opt).name_e">
                                            </multiselect>
                                            <div v-if="!$v.create.company_id.required" class="invalid-feedback">{{ $t('general.fieldIsRequired') }}</div>
                                            <template v-if="errors.company_id">
                                                <ErrorMessage v-for="(errorMessage,index) in errors.name" :key="index">{{ errorMessage }}</ErrorMessage>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label  class="control-label">
                                                {{ $t('module.module') }}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <multiselect
                                                v-model="$v.create.module_id.$model"
                                                :options="modules.map(type => type.id)"
                                                :custom-label="opt => $i18n.locale == 'ar' ? modules.find(x => x.id == opt).name : modules.find(x => x.id == opt).name_e">
                                            </multiselect>
                                            <div v-if="!$v.create.module_id.required" class="invalid-feedback">{{ $t('general.fieldIsRequired') }}</div>
                                            <template v-if="errors.module_id">
                                                <ErrorMessage v-for="(errorMessage,index) in errors.name" :key="index">{{ errorMessage }}</ErrorMessage>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label  class="control-label">
                                                {{ $t('general.allowed_users_no') }}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input
                                                type="number"
                                                class="form-control "
                                                v-model.trim="$v.create.allowed_users_no.$model"
                                                :class="{
                                                    'is-invalid':$v.create.allowed_users_no.$error || errors.allowed_users_no,
                                                    'is-valid':!$v.create.allowed_users_no.$invalid && !errors.allowed_users_no
                                                }"
                                            />
                                            <template v-if="errors.allowed_users_no">
                                                <ErrorMessage v-for="(errorMessage,index) in errors.allowed_users_no" :key="index">{{ errorMessage }}</ErrorMessage>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="control-label">
                                                {{ $t('general.startDate') }}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <date-picker
                                                v-model="create.custom_date_start"
                                                type="datetime"
                                                :default-value="create.custom_date_start"
                                                @change="start_date"
                                                confirm
                                            ></date-picker>
                                            <div v-if="!$v.create.start_date.required" class="invalid-feedback">{{ $t('general.fieldIsRequired') }}</div>
                                            <template v-if="errors.start_date">
                                                <ErrorMessage v-for="(errorMessage,index) in errors.start_date" :key="index">{{ errorMessage }}</ErrorMessage>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="control-label">
                                                {{ $t('general.endDate') }}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <date-picker
                                                v-model="create.custom_date_end"
                                                type="datetime"
                                                @change="end_date"
                                                confirm
                                            ></date-picker>
                                            <template v-if="errors.end_date">
                                                <ErrorMessage v-for="(errorMessage,index) in errors.end_date" :key="index">{{ errorMessage }}</ErrorMessage>
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
                                    <th v-if="setting.company_id">
                                        <div class="d-flex justify-content-center">
                                            <span>{{ $t('company.company') }}</span>
                                        </div>
                                    </th>
                                    <th v-if="setting.module_id">
                                        <div class="d-flex justify-content-center">
                                            <span>{{ $t('module.module') }}</span>
                                        </div>
                                    </th>
                                    <th  v-if="setting.allowed_users_no">
                                        <div class="d-flex justify-content-center">
                                            <span>{{ $t('general.allowed_users_no') }}</span>
                                            <div class="arrow-sort">
                                                <i class="fas fa-arrow-up" @click="companyModules.sort(sortNumber('allowed_users_no'))"></i>
                                                <i class="fas fa-arrow-down" @click="companyModules.sort(sortNumber('-allowed_users_no'))"></i>
                                            </div>
                                        </div>
                                    </th>
                                    <th v-if="setting.start_date">
                                        <div class="d-flex justify-content-center">
                                            <span>{{ $t('general.startDate') }}</span>
                                            <div class="arrow-sort">
                                                <i class="fas fa-arrow-up" @click="companyModules.sort(SortDate('start_date'))"></i>
                                                <i class="fas fa-arrow-down" @click="companyModules.sort(SortDate('-start_date'))"></i>
                                            </div>
                                        </div>
                                    </th>
                                    <th v-if="setting.end_date">
                                        <div class="d-flex justify-content-center">
                                            <span>{{ $t('general.endDate') }}</span>
                                            <div class="arrow-sort">
                                                <i class="fas fa-arrow-up" @click="companyModules.sort(SortDate('end_date'))"></i>
                                                <i class="fas fa-arrow-down" @click="companyModules.sort(SortDate('-end_date'))"></i>
                                            </div>
                                        </div>
                                    </th>
                                    <th>{{ $t('general.Action') }}</th>
                                    <th><i class="fas fa-ellipsis-v"></i></th>
                                </tr>
                                </thead>
                                <tbody v-if="companyModules.length > 0">
                                <tr
                                    v-for="(data,index) in companyModules"
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
                                    <td  v-if="setting.company_id">
                                        {{ $i18n.locale == 'ar' ? data.company.name : data.company.name_e }}
                                    </td>
                                    <td  v-if="setting.module_id">
                                        {{ $i18n.locale == 'ar' ? data.module.name : data.module.name_e }}
                                    </td>
                                    <td  v-if="setting.allowed_users_no">{{ data.allowed_users_no }}</td>
                                    <td v-if="setting.start_date">{{ formatDate(data.start_date) }}</td>
                                    <td v-if="setting.end_date">{{ formatDate(data.end_date) }}</td>
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
                                                    @click.prevent="deleteCompanyModule(data.id)"
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
                                            :title="$t('companyModule.edit')"
                                            title-class="font-18"
                                            body-class="p-4"
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
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label  class="control-label">
                                                                {{ $t('company.company') }}
                                                                <span class="text-danger">*</span>
                                                            </label>
                                                            <multiselect
                                                                v-model="$v.edit.company_id.$model"
                                                                :options="companies.map(type => type.id)"
                                                                :custom-label="opt => $i18n.locale == 'ar' ? companies.find(x => x.id == opt).name : companies.find(x => x.id == opt).name_e">
                                                            </multiselect>
                                                            <div v-if="!$v.edit.company_id.required" class="invalid-feedback">{{ $t('general.fieldIsRequired') }}</div>
                                                            <template v-if="errors.company_id">
                                                                <ErrorMessage v-for="(errorMessage,index) in errors.name" :key="index">{{ errorMessage }}</ErrorMessage>
                                                            </template>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label  class="control-label">
                                                                {{ $t('module.module') }}
                                                                <span class="text-danger">*</span>
                                                            </label>
                                                            <multiselect
                                                                v-model="$v.edit.module_id.$model"
                                                                :options="modules.map(type => type.id)"
                                                                :custom-label="opt => $i18n.locale == 'ar' ? modules.find(x => x.id == opt).name : modules.find(x => x.id == opt).name_e">
                                                            </multiselect>
                                                            <div v-if="!$v.edit.module_id.required" class="invalid-feedback">{{ $t('general.fieldIsRequired') }}</div>
                                                            <template v-if="errors.module_id">
                                                                <ErrorMessage v-for="(errorMessage,index) in errors.name" :key="index">{{ errorMessage }}</ErrorMessage>
                                                            </template>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label  class="control-label">
                                                                {{ $t('general.allowed_users_no') }}
                                                                <span class="text-danger">*</span>
                                                            </label>
                                                            <input
                                                                type="number"
                                                                class="form-control "
                                                                v-model.trim="$v.edit.allowed_users_no.$model"
                                                                :class="{
                                                                    'is-invalid':$v.edit.allowed_users_no.$error || errors.allowed_users_no,
                                                                    'is-valid':!$v.edit.allowed_users_no.$invalid && !errors.allowed_users_no
                                                                }"
                                                            />
                                                            <template v-if="errors.allowed_users_no">
                                                                <ErrorMessage v-for="(errorMessage,index) in errors.allowed_users_no" :key="index">{{ errorMessage }}</ErrorMessage>
                                                            </template>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label class="control-label">
                                                                {{ $t('general.startDate') }}
                                                                <span class="text-danger">*</span>
                                                            </label>
                                                            <date-picker
                                                                v-model="edit.custom_date_start"
                                                                type="datetime"
                                                                :default-value="edit.custom_date_start"
                                                                @change="start_date"
                                                                confirm
                                                            ></date-picker>
                                                            <div v-if="!$v.edit.start_date.required" class="invalid-feedback">{{ $t('general.fieldIsRequired') }}</div>
                                                            <template v-if="errors.start_date">
                                                                <ErrorMessage v-for="(errorMessage,index) in errors.start_date" :key="index">{{ errorMessage }}</ErrorMessage>
                                                            </template>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label class="control-label">
                                                                {{ $t('general.endDate') }}
                                                                <span class="text-danger">*</span>
                                                            </label>
                                                            <date-picker
                                                                v-model="edit.custom_date_end"
                                                                type="datetime"
                                                                @change="end_date"
                                                                confirm
                                                            ></date-picker>
                                                            <template v-if="errors.end_date">
                                                                <ErrorMessage v-for="(errorMessage,index) in errors.end_date" :key="index">{{ errorMessage }}</ErrorMessage>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
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

<style>
.modal-body {
    padding: 2.25rem !important;
}
</style>
