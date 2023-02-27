<script>
import Layout from "../../layouts/main";
import PageHeader from "../../../components/Page-header";
import adminApi from "../../../api/adminAxios";
import Switches from "vue-switches";
import {required, minLength, maxLength, integer, alpha} from "vuelidate/lib/validators";
import Swal from "sweetalert2";
import ErrorMessage from "../../../components/widgets/errorMessage";
import loader from "../../../components/loader";
import {dynamicSortString} from "../../../helper/tableSort";

/**
 * Advanced Table component
 */

export default {
    page: {
        title: "Buttons",
        meta: [{name: "description", content: 'Buttons'}],
    },
    components: {
        Layout,
        PageHeader,
        Switches,
        ErrorMessage,
        loader,
    },
    data() {
        return {
            per_page: 50,
            search: '',
            debounce: {},
            buttonsPagination: {},
            buttons: [],
            isLoader: false,
            create: {
                name: '',
                name_e: '',
                media: [],
            },
            edit: {
                name: '',
                name_e: '',
                old_media: []
            },
            errors: {},
            isCheckAll: false,
            checkAll: [],
            images: [],
            media: {},
            button_id: null,
            saveImageName: [],
            current_page: 1,
            showPhoto: './images/img-1.png',
            changeImage: false,
            setting: {
                name: true,
                name_e: true,
            },
            idDelete: null,
            filterSetting: ['name', 'name_e']
        }
    },
    validations: {
        create: {
            name: {required, minLength: minLength(2), maxLength: maxLength(100),},
            name_e: {required, minLength: minLength(2), maxLength: maxLength(100)},
            media: {}
        },
        edit: {
            name: {required, minLength: minLength(2), maxLength: maxLength(100), },
            name_e: {required, minLength: minLength(2), maxLength: maxLength(100), },
            media: {}
        },
    },
    watch: {
        /**
         * watch per_page
         */
        per_page(after, befour) {
            this.getData();
        },
        /**
         * watch search
         */
        search(after, befour) {
            clearTimeout(this.debounce);
            this.debounce = setTimeout(() => {
                this.getData();
            }, 400);
        },
        /**
         * watch check All table
         */
        isCheckAll(after, befour) {
            if (after) {
                this.buttons.forEach(el => {
                    if (!this.checkAll.includes(el.id)) {
                        this.checkAll.push(el.id);
                    }
                });
            } else {
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
         *  start get Data countrie && pagination
         */
        getData(page = 1) {
            this.isLoader = true;

            let filter = '';
            for (let i = 0; i < this.filterSetting.length; ++i) {
                filter += `columns[${i}]=${this.filterSetting[i]}&`;
            }

            adminApi.get(`/buttons?page=${page}&per_page=${this.per_page}&search=${this.search}&${filter}`)
                .then((res) => {
                    let l = res.data;
                    this.buttons = l.data;
                    this.buttonsPagination = l.pagination;
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
        getDataCurrentPage(page = 1) {
            if (this.current_page <= this.buttonsPagination.last_page && this.current_page != this.buttonsPagination.current_page && this.current_page) {
                this.isLoader = true;
                let filter = '';
                for (let i = 0; i < this.filterSetting.length; ++i) {
                    filter += `columns[${i}]=${this.filterSetting[i]}&`;
                }
                adminApi.get(`/buttons?page=${page}&per_page=${this.per_page}&search=${this.search}&${filter}`)
                    .then((res) => {
                        let l = res.data;
                        this.buttons = l.data;
                        this.buttonsPagination = l.pagination;
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
         *  end get Data countrie && pagination
         */
        /**
         *  start delete countrie
         */
        deleteCountry(id) {
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

                    adminApi.delete(`/buttons/${id}`)
                        .then((res) => {
                            this.getData();
                            this.checkAll = [];
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
         *  end delete countrie
         */
        /**
         *  reset Modal (create)
         */
        resetModalHidden() {
            this.create = {
                name: '',
                name_e: '',
                media: null
            };
            this.$nextTick(() => {
                this.$v.$reset()
            });
            this.images = [];
            this.button_id = null;
            this.errors = {};
            this.$bvModal.hide(`create`);
        },
        /**
         *  hidden Modal (create)
         */
        resetModal() {
            this.create = {
                name: '',
                name_e: '',
            };
            this.showPhoto =  './images/img-1.png';
            this.$nextTick(() => {
                this.$v.$reset()
            });
            this.button_id = null;
            this.media = {};
            this.images = [];
            this.errors = {};
        },
        /**
         *  create countrie
         */
        resetForm(){
            this.create = {
                name: '',
                name_e: '',
            };
            this.$nextTick(() => {
                this.$v.$reset()
            });
            this.button_id = null;
            this.media = {};
            this.images = [];
        },
        AddSubmit() {

            if(!this.create.name){ this.create.name = this.create.name_e}
            if(!this.create.name_e){ this.create.name_e = this.create.name}

            this.$v.create.$touch();

            if (this.$v.create.$invalid) {
                return;
            } else {
                this.isLoader = true;
                this.errors = {};

                adminApi.post(`/buttons`, this.create)
                    .then((res) => {
                        this.button_id = res.data.data.id;
                        setTimeout(() => {
                            Swal.fire({
                                icon: 'success',
                                text: `${this.$t('general.Addedsuccessfully')}`,
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }, 200);
                        this.getData();
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

        },
        /**
         *  edit country
         */
        editSubmit(id) {
            this.$v.edit.$touch();
           if (this.images.length > 0){
               this.images.forEach(e => {this.edit.old_media.push(e.id);});
           }
            if (this.$v.edit.$invalid) {
                return;
            } else {
                this.isLoader = true;
                this.errors = {};
                adminApi.put(`/buttons/${id}`, this.edit)
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
                        }, 500);
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
        },
        /*
        *  log country
        * */
        log(id) {

            adminApi.get(`/buttons/logs/${id}`)
                .then((res) => {

                    console.log(res.data.data);

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
                })

        },
        /**
         *   show Modal (edit)
         */
        resetModalEdit(id) {
            let country = this.buttons.find(e => id == e.id);
            this.button_id = id;
            this.edit.name = country.name;
            this.edit.name_e = country.name_e;
            this.images = country.media ?? [];
            this.showPhoto = this.images.length > 0 ? this.images[this.images.length - 1].webp : './images/img-1.png';
            this.errors = {};
        },
        /**
         *  hidden Modal (edit)
         */
        resetModalHiddenEdit(id) {
            this.errors = {};
            this.edit = {
                name: '',
                name_e: '',
                old_media: []
            };
            this.button_id = null;
            this.images = [];
        },
        /**
         *  start  dynamicSortString
         */
        sortString(value) {
            return dynamicSortString(value);
        },
        /**
         *  start  ckeckRow
         */
        checkRow(id) {
            if (!this.checkAll.includes(id)) {
                this.checkAll.push(id);
            } else {
                let index = this.checkAll.indexOf(id);
                this.checkAll.splice(index, 1);
            }
        },
        /**
         *  end  ckeckRow
         */
        moveInput(tag, c, index) {
            document.querySelector(`${tag}[data-${c}='${index}']`).focus()
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
                            this.images.forEach(e => old_media.push(e.id));
                            let new_media = [];
                            res.data.data.forEach(e => new_media.push(e.id));

                            adminApi.put(`/buttons/${this.button_id}`,{old_media,'media':new_media})
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

                                    adminApi.put(`/buttons/${this.button_id}`,{old_media,'media':new_media})
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
            adminApi.put(`/buttons/${this.button_id}`,{old_media})
                .then((res) => {
                    this.images =  res.data.data.media ?? [];
                    this.showPhoto = this.images.length > 0 ? this.images[this.images.length - 1].webp : './images/img-1.png';
                })
                .catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: `${this.$t('general.Error')}`,
                        text: `${this.$t('general.Thereisanerrorinthesystem')}`,
                    });
                });
        },
        /**
         *  end Image ceate
         */
    },
};
</script>

<template>
    <Layout>
        <PageHeader/>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- start search -->
                        <div class="row justify-content-between align-items-center mb-2">
                            <h4 class="header-title"> {{ $t('module.ButtonsTable') }}</h4>
                            <div class="col-xs-10 col-md-9 col-lg-7" style="font-weight: 500">

                                <div class="d-inline-block" style="width: 22.2%;">
                                    <!-- Basic dropdown -->
                                    <b-dropdown variant="primary" :text="$t('general.searchSetting')" ref="dropdown"
                                                class="btn-block setting-search">
                                        <b-form-checkbox v-model="filterSetting" value="name" class="mb-1">
                                            {{ $t('general.Name') }}
                                        </b-form-checkbox>
                                        <b-form-checkbox v-model="filterSetting" value="name_e" class="mb-1">
                                            {{ $t('general.Name_en') }}
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
                        <!-- end search -->

                        <div class="row justify-content-between align-items-center mb-2 px-1">
                            <div class="col-md-3 d-flex align-items-center mb-1 mb-xl-0">
                                <!-- start create and printer -->
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
                                        @click.prevent="deleteCountry(checkAll)"
                                    >
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <!-- end mult delete  -->
                                    <!--  start one delete  -->
                                    <button
                                        class="custom-btn-dowonload"
                                        v-if="checkAll.length == 1"
                                        @click.prevent="deleteCountry(checkAll)"
                                    >
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <!--  end one delete  -->
                                </div>
                                <!-- end create and printer -->
                            </div>
                            <div
                                class="col-xs-10 col-md-9 col-lg-7 d-flex align-items-center  justify-content-end"
                            >
                                <div class="d-fex">
                                    <!-- start filter and setting -->
                                    <div class="d-inline-block">
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


                                            <div class="d-flex justify-content-end">
                                                <a href="javascript:void(0)" class="btn btn-primary btn-sm">{{$t('general.Apply')}}</a>
                                            </div>
                                        </b-dropdown>
                                        <!-- Basic dropdown -->
                                    </div>
                                    <!-- end filter and setting -->

                                    <!-- start Pagination -->
                                    <div class="d-inline-flex align-items-center pagination-custom">
                                        <div class="d-inline-block" style="font-size:13px;">
                                            {{ buttonsPagination.from }}-{{ buttonsPagination.to }} /
                                            {{ buttonsPagination.total }}
                                        </div>
                                        <div class="d-inline-block">
                                            <a
                                                href="javascript:void(0)"
                                                :style="{'pointer-events':buttonsPagination.current_page == 1 ? 'none': ''}"
                                                @click.prevent="getData(buttonsPagination.current_page - 1)"
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
                                                :style="{'pointer-events':buttonsPagination.last_page == buttonsPagination.current_page ? 'none': ''}"
                                                @click.prevent="getData(buttonsPagination.current_page + 1)"
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
                            :title="$t('general.addButton')"
                            title-class="font-18"
                            size="lg"
                            body-class="paddingUnset"
                            :hide-footer="true"
                            @show="resetModal"
                            @hidden="resetModalHidden"
                        >
                            <form>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="mt-1 d-flex justify-content-end">
                                            <!-- Emulate built in modal footer ok and cancel button actions -->
                                            <b-button
                                                variant="success"
                                                :disabled="!button_id"
                                                @click.prevent="resetForm"
                                                type="button" :class="['font-weight-bold px-2',button_id?'mx-2': '']"
                                            >
                                                {{ $t('general.AddNewRecord') }}
                                            </b-button>

                                            <template v-if="!button_id">
                                                <b-button
                                                    variant="success"
                                                    type="button" class="mx-1 font-weight-bold px-3"
                                                    v-if="!isLoader"
                                                    @click.prevent="AddSubmit"
                                                >
                                                    {{ $t('general.Add') }}
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
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="field-1" class="control-label">
                                                                    {{ $t('general.Name') }}
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <div dir="rtl">
                                                                    <input
                                                                        type="text"
                                                                        class="form-control arabicInput"
                                                                        data-create="1"
                                                                        @keypress.enter="moveInput('input','create',2)"
                                                                        v-model="$v.create.name.$model"
                                                                        :class="{
                                                                        'is-invalid':$v.create.name.$error || errors.name,
                                                                        'is-valid':!$v.create.name.$invalid && !errors.name
                                                                    }"
                                                                        id="field-1"
                                                                    />
                                                                </div>
                                                                <div v-if="!$v.create.name.minLength"
                                                                     class="invalid-feedback">
                                                                    {{ $t('general.Itmustbeatleast') }}
                                                                    {{ $v.create.name.$params.minLength.min }}
                                                                    {{ $t('general.letters') }}
                                                                </div>
                                                                <div v-if="!$v.create.name.maxLength"
                                                                     class="invalid-feedback">
                                                                    {{ $t('general.Itmustbeatmost') }}
                                                                    {{ $v.create.name.$params.maxLength.max }}
                                                                    {{ $t('general.letters') }}
                                                                </div>

                                                                <template v-if="errors.name">
                                                                    <ErrorMessage
                                                                        v-for="(errorMessage,index) in errors.name"
                                                                        :key="index">{{ errorMessage }}
                                                                    </ErrorMessage>
                                                                </template>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="field-2" class="control-label">
                                                                    {{ $t('general.Name_en') }}
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <div dir="ltr">
                                                                    <input
                                                                        type="text"
                                                                        class="form-control englishInput"
                                                                        data-create="2"
                                                                        @keypress.enter="moveInput('input','create',3)"
                                                                        v-model="$v.create.name_e.$model"
                                                                        :class="{
                                                                            'is-invalid':$v.create.name_e.$error || errors.name_e,
                                                                            'is-valid':!$v.create.name_e.$invalid && !errors.name_e
                                                                        }"
                                                                        id="field-2"
                                                                    />
                                                                </div>
                                                                <div v-if="!$v.create.name_e.minLength"
                                                                     class="invalid-feedback">
                                                                    {{ $t('general.Itmustbeatleast') }}
                                                                    {{ $v.create.name_e.$params.minLength.min }}
                                                                    {{ $t('general.letters') }}
                                                                </div>
                                                                <div v-if="!$v.create.name_e.maxLength"
                                                                     class="invalid-feedback">
                                                                    {{ $t('general.Itmustbeatmost') }}
                                                                    {{ $v.create.name_e.$params.maxLength.max }}
                                                                    {{ $t('general.letters') }}
                                                                </div>
                                                                <template v-if="errors.name_e">
                                                                    <ErrorMessage
                                                                        v-for="(errorMessage,index) in errors.name_e"
                                                                        :key="index">{{ errorMessage }}
                                                                    </ErrorMessage>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </b-tab>
                                        <b-tab  :disabled="!button_id"  :title="$t('general.ImageUploads')">
                                            <div class="row">
                                                <input
                                                    accept="image/png, image/gif, image/jpeg, image/jpg"
                                                    type="file"
                                                    id="uploadImageCreate"
                                                    @change.prevent="onImageChanged"
                                                    class="input-file-upload position-absolute"
                                                    :class="[
                                                            'd-none'
                                                            ,{'is-invalid':$v.create.media.$error || errors.media,
                                                            'is-valid':!$v.create.media.$invalid && !errors.media}
                                                        ]"
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
                            <loader size="large" v-if="isLoader"/>
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
                                                <i class="fas fa-arrow-up"
                                                   @click="buttons.sort(sortString('name'))"></i>
                                                <i class="fas fa-arrow-down"
                                                   @click="buttons.sort(sortString('-name'))"></i>
                                            </div>
                                        </div>
                                    </th>
                                    <th v-if="setting.name_e">
                                        <div class="d-flex justify-content-center">
                                            <span>{{ $t('general.Name_en') }}</span>
                                            <div class="arrow-sort">
                                                <i class="fas fa-arrow-up"
                                                   @click="buttons.sort(sortString('name_e'))"></i>
                                                <i class="fas fa-arrow-down"
                                                   @click="buttons.sort(sortString('-name_e'))"></i>
                                            </div>
                                        </div>
                                    </th>
                                    <th>
                                        {{ $t('general.Action') }}
                                    </th>
                                    <th><i class="fas fa-ellipsis-v"></i></th>
                                </tr>
                                </thead>
                                <tbody v-if="buttons.length > 0">
                                <tr
                                    @click.capture="checkRow(data.id)"
                                    @dblclick.prevent="$bvModal.show(`modal-edit-${data.id}`)"
                                    v-for="(data,index) in buttons"
                                    :key="data.id"
                                    class="body-tr-custom"
                                >
                                    <td>
                                        <div class="form-check custom-control" style="min-height: 1.9em;">
                                            <input
                                                style="width: 17px;height: 17px;"
                                                class="form-check-input"
                                                type="checkbox"
                                                v-model="checkAll"
                                                :value="data.id"
                                            >
                                        </div>
                                    </td>
                                    <td v-if="setting.name">
                                        <h5 class="m-0 font-weight-normal">{{ data.name }}</h5>
                                    </td>
                                    <td v-if="setting.name_e">
                                        <h5 class="m-0 font-weight-normal">{{ data.name_e }}</h5>
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
                                                    @click.prevent="deleteCountry(data.id)"
                                                >
                                                    <div
                                                        class="d-flex justify-content-between align-items-center text-black">
                                                        <span>{{ $t('general.delete') }}</span>
                                                        <i class="fas fa-times text-danger"></i>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>

                                        <!--  edit   -->
                                        <b-modal
                                            :id="`modal-edit-${data.id}`"
                                            :title="$t('general.editButton')"
                                            title-class="font-18"
                                            body-class="paddingUnset"
                                            size="lg"
                                            :ref="`edit-${data.id}`"
                                            :hide-footer="true"
                                            @show="resetModalEdit(data.id)"
                                            @hidden="resetModalHiddenEdit(data.id)"
                                        >
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="mt-1 d-flex justify-content-end">
                                                        <!-- Emulate built in modal footer ok and cancel button actions -->
                                                        <b-button
                                                            variant="success"
                                                            @click.prevent="editSubmit(data.id)"
                                                            type="button" class="mx-1 font-weight-bold px-3"
                                                            v-if="!isLoader"
                                                        >
                                                            {{ $t('general.Add') }}
                                                        </b-button>

                                                        <b-button variant="success" class="mx-1" disabled v-else>
                                                            <b-spinner small></b-spinner>
                                                            <span class="sr-only">{{ $t('login.Loading') }}...</span>
                                                        </b-button>

                                                        <b-button variant="danger" class="font-weight-bold" type="button" @click.prevent="$bvModal.hide(`modal-edit-${data.id}`)">
                                                            {{ $t('general.Cancel') }}
                                                        </b-button>
                                                    </div>
                                                </div>
                                                <b-tabs nav-class="nav-tabs nav-bordered">
                                                    <b-tab :title="$t('general.DataEntry')" active>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="edit-1" class="control-label">
                                                                                {{ $t('general.Name') }}
                                                                                <span class="text-danger">*</span>
                                                                            </label>
                                                                            <div dir="rtl">
                                                                                <input
                                                                                    type="text"
                                                                                    class="form-control arabicInput"
                                                                                    data-edit="1"
                                                                                    @keypress.enter="moveInput('input','edit',2)"
                                                                                    v-model="$v.edit.name.$model"
                                                                                    :class="{
                                                                                        'is-invalid':$v.edit.name.$error || errors.name,
                                                                                        'is-valid':!$v.edit.name.$invalid && !errors.name
                                                                                    }"
                                                                                    id="edit-1"
                                                                                />
                                                                            </div>
                                                                            <div v-if="!$v.edit.name.minLength"
                                                                                 class="invalid-feedback">
                                                                                {{ $t('general.Itmustbeatleast') }}
                                                                                {{ $v.create.name.$params.minLength.min }}
                                                                                {{ $t('general.letters') }}
                                                                            </div>
                                                                            <div v-if="!$v.edit.name.maxLength"
                                                                                 class="invalid-feedback">
                                                                                {{ $t('general.Itmustbeatmost') }}
                                                                                {{ $v.edit.name.$params.maxLength.max }}
                                                                                {{ $t('general.letters') }}
                                                                            </div>
                                                                            <div v-if="!$v.edit.name.alphaArabic"
                                                                                 class="invalid-feedback">{{
                                                                                    $t('general.alphaArabic')
                                                                                }}
                                                                            </div>
                                                                            <template v-if="errors.name">
                                                                                <ErrorMessage
                                                                                    v-for="(errorMessage,index) in errors.name"
                                                                                    :key="index">{{ errorMessage }}
                                                                                </ErrorMessage>
                                                                            </template>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="edit-2" class="control-label">
                                                                                {{ $t('general.Name_en') }}
                                                                                <span class="text-danger">*</span>
                                                                            </label>
                                                                            <div dir="ltr">
                                                                                <input
                                                                                    type="text"
                                                                                    class="form-control englishInput"
                                                                                    data-edit="2"
                                                                                    @keypress.enter="moveInput('input','edit',3)"
                                                                                    v-model="$v.edit.name_e.$model"
                                                                                    :class="{
                                                                                        'is-invalid':$v.edit.name_e.$error || errors.name_e,
                                                                                        'is-valid':!$v.edit.name_e.$invalid && !errors.name_e
                                                                                    }"
                                                                                    id="edit-2"
                                                                                />
                                                                            </div>
                                                                            <div v-if="!$v.edit.name_e.minLength"
                                                                                 class="invalid-feedback">
                                                                                {{ $t('general.Itmustbeatleast') }}
                                                                                {{ $v.edit.name_e.$params.minLength.min }}
                                                                                {{ $t('general.letters') }}
                                                                            </div>
                                                                            <div v-if="!$v.edit.name_e.maxLength"
                                                                                 class="invalid-feedback">
                                                                                {{ $t('general.Itmustbeatmost') }}
                                                                                {{ $v.edit.name_e.$params.maxLength.max }}
                                                                                {{ $t('general.letters') }}
                                                                            </div>
                                                                            <div v-if="!$v.edit.name_e.alphaEnglish"
                                                                                 class="invalid-feedback">{{
                                                                                    $t('general.alphaEnglish')
                                                                                }}
                                                                            </div>
                                                                            <template v-if="errors.name_e">
                                                                                <ErrorMessage
                                                                                    v-for="(errorMessage,index) in errors.name_e"
                                                                                    :key="index">{{ errorMessage }}
                                                                                </ErrorMessage>
                                                                            </template>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </b-tab>
                                                    <b-tab  :title="$t('general.ImageUploads')">
                                                        <div class="row">
                                                            <input
                                                                accept="image/png, image/gif, image/jpeg, image/jpg"
                                                                type="file"
                                                                id="uploadImageEdit"
                                                                @change.prevent="onImageChanged"
                                                                class="input-file-upload position-absolute"
                                                                :class="[
                                                            'd-none'
                                                            ,{'is-invalid':$v.edit.media.$error || errors.media,
                                                            'is-valid':!$v.edit.media.$invalid && !errors.media}
                                                        ]"
                                                            >
                                                            <div class="col-md-8 my-1">
                                                                <!-- file upload -->
                                                                <div class="row align-content-between" style="width: 100%;height: 100%">
                                                                    <div class="col-12">
                                                                        <div class="d-flex flex-wrap">
                                                                            <div
                                                                                class="dropzone-previews col-4 position-relative mb-2"
                                                                                v-for="(photo,index) in images"
                                                                            >
                                                                                <div :class="['card mb-0 shadow-none border',(images.length - 1) == index ? 'bg-primary': '']">
                                                                                    <div class="p-2">
                                                                                        <div class="row align-items-center">
                                                                                            <div class="col-auto" @click="showPhoto = photo.webp">
                                                                                                <img
                                                                                                    data-dz-thumbnail
                                                                                                    :src="photo.webp" class="avatar-sm rounded bg-light"
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
                                                                                                :class="['btn-danger text-muted dropzone-close',
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
                                                                    <img :src="showPhoto" class="img-thumbnail" @error="src='./images/img-1.png'" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </b-tab>
                                                </b-tabs>
                                            </div>
                                        </b-modal>
                                        <!--  /edit   -->
                                    </td>
                                    <td>
                                        <i
                                            @mouseenter="log(data.id)"
                                            class="fe-info"
                                        ></i>
                                    </td>
                                </tr>
                                </tbody>
                                <tbody v-else>
                                <tr>
                                    <th class="text-center" colspan="11">{{ $t('general.notDataFound') }}</th>
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







