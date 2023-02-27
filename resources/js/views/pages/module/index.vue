<script>
import Layout from "../../layouts/main";
import PageHeader from "../../../components/Page-header";
import adminApi from "../../../api/adminAxios";
import Switches from "vue-switches";
import {
    required,
    minLength,
    maxLength,
    integer,
} from "vuelidate/lib/validators";
import Swal from "sweetalert2";
import ErrorMessage from "../../../components/widgets/errorMessage";
import loader from "../../../components/loader";
import { dynamicSortString } from "../../../helper/tableSort";
import Multiselect from "vue-multiselect";
import Templates from "../email/templates.vue";

/**
 * Advanced Table component
 */
export default {
    page: {
        title: "Module",
        meta: [{ name: "description", content: "Module" }],
    },
    components: {
        Layout,
        PageHeader,
        Switches,
        ErrorMessage,
        loader,
        Multiselect,
        Templates,
    },
    updated() {
        $(".englishInput").keypress(function (event) {
            var ew = event.which;
            if (ew == 32) return true;
            if (48 <= ew && ew <= 57) return true;
            if (65 <= ew && ew <= 90) return true;
            if (97 <= ew && ew <= 122) return true;
            return false;
        });
        $(".arabicInput").keypress(function (event) {
            var ew = event.which;
            if (ew == 32) return true;
            if (48 <= ew && ew <= 57) return false;
            if (65 <= ew && ew <= 90) return false;
            if (97 <= ew && ew <= 122) return false;
            return true;
        });
    },
    data() {
        return {
            per_page: 50,
            search: "",
            debounce: {},
            modulesPagination: {},
            modules: [],
            enabled3: false,
            isLoader: false,
            rootNodes: [],
            childNodes: [],
            create: {
                name: "",
                name_e: "",
                parent_id: null,
                is_active: "active",
                search: "",
            },
            edit: {
                name: "",
                name_e: "",
                parent_id: null,
                is_active: "active",
                search: "",
            },
            setting: {
                name: true,
                name_e: true,
                is_active: true,
            },
            filterSetting: ["name", "name_e"],
            errors: {},
            isCheckAll: false,
            checkAll: [],
            is_disabled: false,
            current_page: 1,
        };
    },
    validations: {
        create: {
            name: {
                required,
                minLength: minLength(3),
                maxLength: maxLength(100),
            },
            name_e: {
                required,
                minLength: minLength(3),
                maxLength: maxLength(100),
            },
            is_active: { required },
        },
        edit: {
            name: {
                required,
                minLength: minLength(3),
                maxLength: maxLength(100),
            },
            name_e: {
                required,
                minLength: minLength(3),
                maxLength: maxLength(100),
            },
            is_active: { required },
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
                this.modules.forEach((el) => {
                    if (!this.checkAll.includes(el.id)) {
                        this.checkAll.push(el.id);
                    }
                });
            } else {
                this.checkAll = [];
            }
        },
    },
    mounted() {
        this.getData();
    },
    methods: {
        /**
         *  get Data module
         */
        getData(page = 1) {
            this.isLoader = true;

            let filter = "";
            for (let i = 0; i > this.filterSetting.length; ++i) {
                filter += `columns[${i}]=${this.filterSetting[i]}&`;
            }

            adminApi
                .get(
                    `/modules?page=${page}&per_page=${this.per_page}&search=${this.search}&${filter}`
                )
                .then((res) => {
                    let l = res.data;
                    this.modules = l.data;
                    this.modulesPagination = l.pagination;
                    this.current_page = l.pagination.current_page;
                })
                .catch((err) => {
                    Swal.fire({
                        icon: "error",
                        title: `${this.$t("general.Error")}`,
                        text: `${this.$t("general.Thereisanerrorinthesystem")}`,
                    });
                })
                .finally(() => {
                    this.isLoader = false;
                });
        },
        getDataCurrentPage() {
            if (
                this.current_page <= this.modulesPagination.last_page &&
                this.current_page != this.modulesPagination.current_page &&
                this.current_page
            ) {
                this.isLoader = true;
                let filter = "";
                for (let i = 0; i > this.filterSetting.length; ++i) {
                    filter += `columns[${i}]=${this.filterSetting[i]}&`;
                }

                adminApi
                    .get(
                        `/modules?page=${this.current_page}&per_page=${this.per_page}&search=${this.search}&${filter}`
                    )
                    .then((res) => {
                        let l = res.data;
                        this.modules = l.data;
                        this.modulesPagination = l.pagination;
                        this.current_page = l.pagination.current_page;
                    })
                    .catch((err) => {
                        Swal.fire({
                            icon: "error",
                            title: `${this.$t("general.Error")}`,
                            text: `${this.$t(
                                "general.Thereisanerrorinthesystem"
                            )}`,
                        });
                    })
                    .finally(() => {
                        this.isLoader = false;
                    });
            }
        },
        /**
         *  delete module
         */
        deleteModule(id) {
            Swal.fire({
                title: `${this.$t("general.Areyousure")}`,
                text: `${this.$t("general.Youwontbeabletoreverthis")}`,
                type: "warning",
                showCancelButton: true,
                confirmButtonText: `${this.$t("general.Yesdeleteit")}`,
                cancelButtonText: `${this.$t("general.Nocancel")}`,
                confirmButtonClass: "btn btn-success mt-2",
                cancelButtonClass: "btn btn-danger ml-2 mt-2",
                buttonsStyling: false,
            }).then((result) => {
                if (result.value) {
                    this.isLoader = true;

                    adminApi
                        .delete(`/modules/${id}`)
                        .then((res) => {
                            this.getData();
                            this.checkAll = [];
                            Swal.fire({
                                icon: "success",
                                title: `${this.$t("general.Deleted")}`,
                                text: `${this.$t(
                                    "general.Yourrowhasbeendeleted"
                                )}`,
                                showConfirmButton: false,
                                timer: 1500,
                            });
                        })
                        .catch((err) => {
                            Swal.fire({
                                icon: "error",
                                title: `${this.$t("general.Error")}`,
                                text: `${this.$t(
                                    "general.Thereisanerrorinthesystem"
                                )}`,
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
        resetModalHidden() {
            this.create = {
                name: "",
                name_e: "",
                parent_id: null,
                is_active: "active",
            };
            this.$nextTick(() => {
                this.$v.$reset();
            });
            this.errors = {};
            this.rootNodes = [];
        },
        /**
         *  hidden Modal (create)
         */
        async resetModal() {
            await this.getRootNodes();
            this.create = {
                name: "",
                name_e: "",
                parent_id: null,
                is_active: "active",
            };
            this.is_disabled = false;
            this.$nextTick(() => {
                this.$v.$reset();
            });
            this.errors = {};
        },
        /**
         *  create module
         */
        resetForm() {
            this.create = {
                name: "",
                name_e: "",
                parent_id: null,
                is_active: "active",
            };
            this.is_disabled = false;
            this.$nextTick(() => {
                this.$v.$reset();
            });
        },
        AddSubmit() {
            if (this.create.name || this.create.name_e) {
                this.create.name = this.create.name
                    ? this.create.name
                    : this.create.name_e;
                this.create.name_e = this.create.name_e
                    ? this.create.name_e
                    : this.create.name;
            }
            this.$v.create.$touch();
            if (this.$v.create.$invalid) {
                return;
            } else {
                this.isLoader = true;
                this.errors = {};
                this.is_disabled = false;
                if (this.create.parent_id == null) {
                    this.create.parent_id = 0;
                }
                adminApi
                    .post(`/modules`, this.create)
                    .then((res) => {
                        this.getData();
                        this.is_disabled = true;
                        this.getRootNodes();
                        setTimeout(() => {
                            Swal.fire({
                                icon: "success",
                                text: `${this.$t("general.Addedsuccessfully")}`,
                                showConfirmButton: false,
                                timer: 1500,
                            });
                        }, 500);
                    })
                    .catch((err) => {
                        if (err.response.data) {
                            this.errors = err.response.data.errors;
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: `${this.$t("general.Error")}`,
                                text: `${this.$t(
                                    "general.Thereisanerrorinthesystem"
                                )}`,
                            });
                        }
                    })
                    .finally(() => {
                        this.isLoader = false;
                    });
            }
        },
        /**
         *  edit module
         */
        editSubmit(id) {
            if (this.edit.name || this.edit.name_e) {
                this.edit.name = this.edit.name
                    ? this.edit.name
                    : this.edit.name_e;
                this.edit.name_e = this.edit.name_e
                    ? this.edit.name_e
                    : this.edit.name;
            }
            this.$v.edit.$touch();
            if (this.$v.edit.$invalid) {
                return;
            } else {
                this.isLoader = true;
                this.errors = {};
                if (!this.edit.parent_id) {
                    this.edit.parent_id = 0;
                }
                let { name, name_e, parent_id, is_active } = this.edit;
                adminApi
                    .put(`/modules/${id}`, {
                        name,
                        name_e,
                        parent_id,
                        is_active,
                    })
                    .then((res) => {
                        this.$bvModal.hide(`modal-edit-${id}`);
                        this.getData();
                        setTimeout(() => {
                            Swal.fire({
                                icon: "success",
                                text: `${this.$t("general.Editsuccessfully")}`,
                                showConfirmButton: false,
                                timer: 1500,
                            });
                        }, 500);
                    })
                    .catch((err) => {
                        if (err.response.data) {
                            this.errors = err.response.data.errors;
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: `${this.$t("general.Error")}`,
                                text: `${this.$t(
                                    "general.Thereisanerrorinthesystem"
                                )}`,
                            });
                        }
                    })
                    .finally(() => {
                        this.isLoader = false;
                    });
            }
        },
        async getRootNodes() {
            await adminApi
                .get(`/modules/root-nodes`)
                .then((res) => {
                    // console.log(this.rootNodes);
                    this.rootNodes = res.data;
                })
                .catch((err) => {
                    Swal.fire({
                        icon: "error",
                        title: `${this.$t("general.Error")}`,
                        text: `${this.$t("general.Thereisanerrorinthesystem")}`,
                    });
                });
        },
        getFirstLevelChildNodes(node) {
            if (!node.collapsed) {
                adminApi
                    .get(`/modules/child-nodes/${node.id}`)
                    .then((res) => {
                        this.rootNodes = this.getUpdatedRootNodes(
                            node,
                            res.data
                        );
                    })
                    .catch((err) => {
                        Swal.fire({
                            icon: "error",
                            title: `${this.$t("general.Error")}`,
                            text: `${this.$t(
                                "general.Thereisanerrorinthesystem"
                            )}`,
                        });
                    });
            } else {
                this.rootNodes = this.getUpdatedRootNodes(node);
            }
        },
        getSecondLevelChildNodes(rootNode, parentNode) {
            if (!parentNode.collapsed) {
                adminApi
                    .get(`/modules/child-nodes/${parentNode.id}`)
                    .then((res) => {
                        this.rootNodes = this.getRootNodesAfterCollapse(
                            rootNode,
                            parentNode,
                            res.data
                        );
                    })
                    .catch((err) => {
                        Swal.fire({
                            icon: "error",
                            title: `${this.$t("general.Error")}`,
                            text: `${this.$t(
                                "general.Thereisanerrorinthesystem"
                            )}`,
                        });
                    });
            } else {
                this.rootNodes = this.getRootNodesAfterCollapse(
                    rootNode,
                    parentNode
                );
            }
        },
        setCreateParentId(node) {
            if (this.create.parent_id != node.id) {
                this.create.parent_id = node.id;
            } else {
                this.create.parent_id = null;
            }
        },
        setUpdateParentId(node) {
            if (this.edit.parent_id != node.id) {
                this.edit.parent_id = node.id;
            } else {
                this.edit.parent_id = null;
            }
        },
        /**
         *   show Modal (edit)
         */
        async resetModalEdit(id) {
            this.getRootNodes();
            let module = this.modules.find((e) => id == e.id);
            this.edit.name = module.name;
            this.edit.name_e = module.name_e;
            this.edit.is_active = module.is_active;
            this.edit.parent_id = module.parent_id;
            this.errors = {};
        },
        /**
         *  hidden Modal (edit)
         */
        resetModalHiddenEdit(id) {
            this.errors = {};
            this.edit = {
                name: "",
                name_e: "",
                parent_id: null,
                is_active: "active",
            };
            this.rootNodes = [];
        },
        getUpdatedRootNodes(parentNode, children) {
            let rootNodes = [...this.rootNodes];
            rootNodes.forEach((node, index) => {
                if (node.id == parentNode.id) {
                    if (parentNode.collapsed) {
                        rootNodes[index].children = [];
                        rootNodes[index].collapsed = false;
                    } else {
                        rootNodes[index].children = children;
                        rootNodes[index].collapsed = true;
                    }
                    return;
                }
            });
            return rootNodes;
        },
        getRootNodesAfterCollapse(parentNode, secondParentNode, children) {
            let rootNodes = [...this.rootNodes];
            rootNodes.forEach((_parentNode, parentIndex) => {
                if (_parentNode.id == parentNode.id) {
                    if (_parentNode.children && _parentNode.children.length) {
                        _parentNode.children.forEach((child, index) => {
                            if (child.id == secondParentNode.id) {
                                if (secondParentNode.collapsed) {
                                    rootNodes[parentIndex].children[
                                        index
                                    ].children = [];
                                    rootNodes[parentIndex].children[
                                        index
                                    ].collapsed = false;
                                } else {
                                    rootNodes[parentIndex].children[
                                        index
                                    ].children = children;
                                    rootNodes[parentIndex].children[
                                        index
                                    ].collapsed = true;
                                }
                                return;
                            }
                        });
                        return;
                    }
                }
            });
            return rootNodes;
        },
        /**
         *  start  dynamicSortString
         */
        sortString(value) {
            return dynamicSortString(value);
        },
        checkRow(id) {
            if (!this.checkAll.includes(id)) {
                this.checkAll.push(id);
            } else {
                let index = this.checkAll.indexOf(id);
                this.checkAll.splice(index, 1);
            }
        },
    },
};
</script>

<template>
    <Layout>
        <PageHeader />
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div
                            class="row justify-content-between align-items-center mb-2"
                        >
                            <h4 class="header-title">
                                {{ $t("module.ModulesTable") }}
                            </h4>
                            <div
                                class="col-xs-10 col-md-9 col-lg-7"
                                style="font-weight: 500"
                            >
                                <div
                                    class="d-inline-block"
                                    style="width: 22.2%"
                                >
                                    <!-- Basic dropdown -->
                                    <b-dropdown
                                        variant="primary"
                                        :text="$t('general.searchSetting')"
                                        ref="dropdown"
                                        class="btn-block setting-search"
                                    >
                                        <b-form-checkbox
                                            v-model="filterSetting"
                                            value="name"
                                            class="mb-1"
                                            >{{
                                                $t("general.Name")
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="filterSetting"
                                            value="name_e"
                                            class="mb-1"
                                            >{{
                                                $t("general.Name_en")
                                            }}</b-form-checkbox
                                        >
                                    </b-dropdown>
                                    <!-- Basic dropdown -->
                                </div>

                                <div
                                    class="d-inline-block position-relative"
                                    style="width: 77%"
                                >
                                    <span
                                        :class="[
                                            'search-custom position-absolute',
                                            $i18n.locale == 'ar'
                                                ? 'search-custom-ar'
                                                : '',
                                        ]"
                                    >
                                        <i class="fe-search"></i>
                                    </span>
                                    <input
                                        class="form-control"
                                        style="
                                            display: block;
                                            width: 93%;
                                            padding-top: 3px;
                                        "
                                        type="text"
                                        v-model.trim="search"
                                        :placeholder="`${$t(
                                            'general.Search'
                                        )}...`"
                                    />
                                </div>
                            </div>
                        </div>

                        <div
                            class="row justify-content-between align-items-center mb-2 px-1"
                        >
                            <div
                                class="col-md-3 d-flex align-items-center mb-1 mb-xl-0"
                            >
                                <b-button
                                    v-b-modal.create
                                    variant="primary"
                                    class="btn-sm mx-1 font-weight-bold"
                                >
                                    {{ $t("general.Create") }}
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
                                        @click="
                                            $bvModal.show(
                                                `modal-edit-${checkAll[0]}`
                                            )
                                        "
                                        v-if="checkAll.length == 1"
                                    >
                                        <i
                                            class="mdi mdi-square-edit-outline"
                                        ></i>
                                    </button>
                                    <!-- start mult delete  -->
                                    <button
                                        class="custom-btn-dowonload"
                                        v-if="checkAll.length > 1"
                                        @click.prevent="deleteModule(checkAll)"
                                    >
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <!-- end mult delete  -->
                                    <!--  start one delete  -->
                                    <button
                                        class="custom-btn-dowonload"
                                        v-if="checkAll.length == 1"
                                        @click.prevent="deleteModule(checkAll)"
                                    >
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <!--  end one delete  -->
                                </div>
                            </div>
                            <div
                                class="col-xs-10 col-md-9 col-lg-7 d-flex align-items-center justify-content-end"
                            >
                                <div>
                                    <b-button
                                        class="mx-1 custom-btn-background"
                                    >
                                        {{ $t("general.filter") }}
                                        <i class="fas fa-filter"></i>
                                    </b-button>
                                    <b-button
                                        class="mx-1 custom-btn-background"
                                    >
                                        {{ $t("general.group") }}
                                        <i class="fe-menu"></i>
                                    </b-button>
                                    <!-- Basic dropdown -->
                                    <b-dropdown
                                        variant="primary"
                                        :html="`${$t(
                                            'general.setting'
                                        )} <i class='fe-settings'></i>`"
                                        ref="dropdown"
                                        class="dropdown-custom-ali"
                                    >
                                        <b-form-checkbox
                                            v-model="setting.name"
                                            class="mb-1"
                                            >{{ $t("general.Name") }}
                                        </b-form-checkbox>
                                        <b-form-checkbox
                                            v-model="setting.name_e"
                                            class="mb-1"
                                        >
                                            {{ $t("general.Name_en") }}
                                        </b-form-checkbox>
                                        <b-form-checkbox
                                            v-model="setting.is_active"
                                            class="mb-1"
                                        >
                                            {{ $t("general.Status") }}
                                        </b-form-checkbox>
                                        <div class="d-flex justify-content-end">
                                            <a
                                                href="javascript:void(0)"
                                                class="btn btn-primary btn-sm"
                                                >{{ $t("general.Apply") }}</a
                                            >
                                        </div>
                                    </b-dropdown>
                                    <!-- Basic dropdown -->
                                    <!-- start Pagination -->
                                    <div
                                        class="d-inline-flex align-items-center pagination-custom"
                                    >
                                        <div
                                            class="d-inline-block"
                                            style="font-size: 15px"
                                        >
                                            {{ modulesPagination.from }}-{{
                                                modulesPagination.to
                                            }}
                                            /
                                            {{ modulesPagination.total }}
                                        </div>
                                        <div class="d-inline-block">
                                            <a
                                                href="javascript:void(0)"
                                                :style="{
                                                    'pointer-events':
                                                        modulesPagination.current_page ==
                                                        1
                                                            ? 'none'
                                                            : '',
                                                }"
                                                @click.prevent="
                                                    getData(
                                                        modulesPagination.current_page -
                                                            1
                                                    )
                                                "
                                            >
                                                <span>&lt;</span>
                                            </a>
                                            <input
                                                type="text"
                                                @keyup.enter="
                                                    getDataCurrentPage()
                                                "
                                                v-model="current_page"
                                                class="pagination-current-page"
                                            />
                                            <a
                                                href="javascript:void(0)"
                                                :style="{
                                                    'pointer-events':
                                                        modulesPagination.last_page ==
                                                        modulesPagination.current_page
                                                            ? 'none'
                                                            : '',
                                                }"
                                                @click.prevent="
                                                    getData(
                                                        modulesPagination.current_page +
                                                            1
                                                    )
                                                "
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
                            :title="$t('module.addmodule')"
                            title-class="font-18"
                            body-class="p-4 "
                            :hide-footer="true"
                            size="lg"
                            @show="resetModal"
                            @hidden="resetModalHidden"
                        >
                            <form>
                                <div class="mb-3 d-flex justify-content-end">
                                    <b-button
                                        variant="success"
                                        :disabled="!is_disabled"
                                        @click.prevent="resetForm"
                                        type="button"
                                        :class="[
                                            'font-weight-bold px-2',
                                            is_disabled ? 'mx-2' : '',
                                        ]"
                                    >
                                        {{ $t("general.AddNewRecord") }}
                                    </b-button>
                                    <template v-if="!is_disabled">
                                        <b-button
                                            variant="success"
                                            type="submit"
                                            class="mx-1"
                                            v-if="!isLoader"
                                            @click.prevent="AddSubmit"
                                        >
                                            {{ $t("general.Add") }}
                                        </b-button>

                                        <b-button
                                            variant="success"
                                            class="mx-1"
                                            disabled
                                            v-else
                                        >
                                            <b-spinner small></b-spinner>
                                            <span class="sr-only"
                                                >{{
                                                    $t("login.Loading")
                                                }}...</span
                                            >
                                        </b-button>
                                    </template>
                                    <b-button
                                        @click.prevent="$bvModal.hide(`create`)"
                                        variant="danger"
                                        type="button"
                                    >
                                        {{ $t("general.Cancel") }}
                                    </b-button>
                                </div>
                                <div class="row">
                                    <div
                                        class="col-8"
                                        :class="
                                            $i18n.locale == 'ar' ? 'rtl' : 'ltr'
                                        "
                                    >
                                        <ul id="myUL">
                                            <li
                                                v-for="node in rootNodes"
                                                :key="node.id"
                                            >
                                                <span>
                                                    <i
                                                        @click="
                                                            getFirstLevelChildNodes(
                                                                node
                                                            )
                                                        "
                                                        v-if="node.haveChildren"
                                                        :class="
                                                            node.collapsed
                                                                ? 'fa fa-caret-down'
                                                                : $i18n.locale ==
                                                                  'ar'
                                                                ? 'fa fa-caret-left'
                                                                : 'fa fa-caret-right'
                                                        "
                                                    ></i>
                                                    <span
                                                        @click="
                                                            setCreateParentId(
                                                                node
                                                            )
                                                        "
                                                        :class="{
                                                            'without-children':
                                                                !node.haveChildren,
                                                            active:
                                                                node.id ==
                                                                create.parent_id,
                                                        }"
                                                    >
                                                        {{
                                                            $i18n.locale == "ar"
                                                                ? node.name
                                                                : node.name_e
                                                        }}
                                                    </span>
                                                </span>
                                                <ul
                                                    v-if="
                                                        node.children &&
                                                        node.children.length
                                                    "
                                                    class="nested"
                                                >
                                                    <li
                                                        v-for="childNode in node.children"
                                                        :key="childNode.id"
                                                    >
                                                        <span>
                                                            <i
                                                                @click="
                                                                    getSecondLevelChildNodes(
                                                                        node,
                                                                        childNode
                                                                    )
                                                                "
                                                                v-if="
                                                                    childNode.haveChildren
                                                                "
                                                                :class="
                                                                    childNode.collapsed
                                                                        ? 'fa fa-caret-down'
                                                                        : $i18n.locale ==
                                                                          'ar'
                                                                        ? 'fa fa-caret-left'
                                                                        : 'fa fa-caret-right'
                                                                "
                                                            >
                                                            </i>
                                                            <span
                                                                @click="
                                                                    setCreateParentId(
                                                                        childNode
                                                                    )
                                                                "
                                                                :class="{
                                                                    'without-children':
                                                                        !childNode.haveChildren,
                                                                    active:
                                                                        childNode.id ==
                                                                        create.parent_id,
                                                                }"
                                                            >
                                                                {{
                                                                    $i18n.locale ==
                                                                    "ar"
                                                                        ? childNode.name
                                                                        : childNode.name_e
                                                                }}
                                                            </span>
                                                        </span>
                                                        <ul
                                                            v-if="
                                                                childNode.children &&
                                                                childNode
                                                                    .children
                                                                    .length
                                                            "
                                                            class="nested"
                                                        >
                                                            <li
                                                                v-for="child in childNode.children"
                                                                :key="child.id"
                                                            >
                                                                {{
                                                                    $i18n.locale ==
                                                                    "ar"
                                                                        ? child.name
                                                                        : child.name_e
                                                                }}
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-4">
                                        <div class="row">
                                            <div
                                                class="col-12 direction"
                                                dir="rtl"
                                            >
                                                <div class="form-group">
                                                    <label
                                                        for="field-1"
                                                        class="control-label"
                                                    >
                                                        {{ $t("general.Name") }}
                                                        <span
                                                            class="text-danger"
                                                            >*</span
                                                        >
                                                    </label>
                                                    <input
                                                        type="text"
                                                        class="form-control arabicInput"
                                                        v-model="
                                                            $v.create.name
                                                                .$model
                                                        "
                                                        :class="{
                                                            'is-invalid':
                                                                $v.create.name
                                                                    .$error ||
                                                                errors.name,
                                                            'is-valid':
                                                                !$v.create.name
                                                                    .$invalid &&
                                                                !errors.name,
                                                        }"
                                                        id="field-1"
                                                    />
                                                    <div
                                                        v-if="
                                                            !$v.create.name
                                                                .minLength
                                                        "
                                                        class="invalid-feedback"
                                                    >
                                                        {{
                                                            $t(
                                                                "general.Itmustbeatleast"
                                                            )
                                                        }}
                                                        {{
                                                            $v.create.name
                                                                .$params
                                                                .minLength.min
                                                        }}
                                                        {{
                                                            $t(
                                                                "general.letters"
                                                            )
                                                        }}
                                                    </div>
                                                    <div
                                                        v-if="
                                                            !$v.create.name
                                                                .maxLength
                                                        "
                                                        class="invalid-feedback"
                                                    >
                                                        {{
                                                            $t(
                                                                "general.Itmustbeatmost"
                                                            )
                                                        }}
                                                        {{
                                                            $v.create.name
                                                                .$params
                                                                .maxLength.max
                                                        }}
                                                        {{
                                                            $t(
                                                                "general.letters"
                                                            )
                                                        }}
                                                    </div>
                                                    <template
                                                        v-if="errors.name"
                                                    >
                                                        <ErrorMessage
                                                            v-for="(
                                                                errorMessage,
                                                                index
                                                            ) in errors.name"
                                                            :key="index"
                                                            >{{
                                                                $t(errorMessage)
                                                            }}</ErrorMessage
                                                        >
                                                    </template>
                                                </div>
                                            </div>
                                            <div
                                                class="col-12 direction-ltr"
                                                dir="ltr"
                                            >
                                                <div class="form-group">
                                                    <label
                                                        for="field-2"
                                                        class="control-label"
                                                    >
                                                        {{
                                                            $t(
                                                                "general.Name_en"
                                                            )
                                                        }}
                                                        <span
                                                            class="text-danger"
                                                            >*</span
                                                        >
                                                    </label>
                                                    <input
                                                        type="text"
                                                        class="form-control englishInput"
                                                        v-model="
                                                            $v.create.name_e
                                                                .$model
                                                        "
                                                        :class="{
                                                            'is-invalid':
                                                                $v.create.name_e
                                                                    .$error ||
                                                                errors.name_e,
                                                            'is-valid':
                                                                !$v.create
                                                                    .name_e
                                                                    .$invalid &&
                                                                !errors.name_e,
                                                        }"
                                                        id="field-2"
                                                    />
                                                    <div
                                                        v-if="
                                                            !$v.create.name_e
                                                                .minLength
                                                        "
                                                        class="invalid-feedback"
                                                    >
                                                        {{
                                                            $t(
                                                                "general.Itmustbeatleast"
                                                            )
                                                        }}
                                                        {{
                                                            $v.create.name_e
                                                                .$params
                                                                .minLength.min
                                                        }}
                                                        {{
                                                            $t(
                                                                "general.letters"
                                                            )
                                                        }}
                                                    </div>
                                                    <div
                                                        v-if="
                                                            !$v.create.name_e
                                                                .maxLength
                                                        "
                                                        class="invalid-feedback"
                                                    >
                                                        {{
                                                            $t(
                                                                "general.Itmustbeatmost"
                                                            )
                                                        }}
                                                        {{
                                                            $v.create.name_e
                                                                .$params
                                                                .maxLength.max
                                                        }}
                                                        {{
                                                            $t(
                                                                "general.letters"
                                                            )
                                                        }}
                                                    </div>
                                                    <template
                                                        v-if="errors.name_e"
                                                    >
                                                        <ErrorMessage
                                                            v-for="(
                                                                errorMessage,
                                                                index
                                                            ) in errors.name_e"
                                                            :key="index"
                                                            >{{
                                                                $t(errorMessage)
                                                            }}</ErrorMessage
                                                        >
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label class="mr-2 mb-2">
                                                        {{
                                                            $t("general.Status")
                                                        }}
                                                        <span
                                                            class="text-danger"
                                                            >*</span
                                                        >
                                                    </label>
                                                    <b-form-group
                                                        :class="{
                                                            'is-invalid':
                                                                $v.create
                                                                    .is_active
                                                                    .$error ||
                                                                errors.is_active,
                                                            'is-valid':
                                                                !$v.create
                                                                    .is_active
                                                                    .$invalid &&
                                                                !errors.is_active,
                                                        }"
                                                    >
                                                        <b-form-radio
                                                            class="d-inline-block"
                                                            v-model="
                                                                $v.create
                                                                    .is_active
                                                                    .$model
                                                            "
                                                            name="some-radios"
                                                            value="active"
                                                            >{{
                                                                $t(
                                                                    "general.Active"
                                                                )
                                                            }}</b-form-radio
                                                        >
                                                        <b-form-radio
                                                            class="d-inline-block m-1"
                                                            v-model="
                                                                $v.create
                                                                    .is_active
                                                                    .$model
                                                            "
                                                            name="some-radios"
                                                            value="inactive"
                                                            >{{
                                                                $t(
                                                                    "general.Inactive"
                                                                )
                                                            }}</b-form-radio
                                                        >
                                                    </b-form-group>
                                                    <template
                                                        v-if="errors.is_active"
                                                    >
                                                        <ErrorMessage
                                                            v-for="(
                                                                errorMessage,
                                                                index
                                                            ) in errors.is_active"
                                                            :key="index"
                                                            >{{
                                                                $t(errorMessage)
                                                            }}
                                                        </ErrorMessage>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </b-modal>
                        <!--  /create   -->

                        <!-- start .table-responsive-->
                        <div
                            class="table-responsive mb-3 custom-table-theme position-relative"
                        >
                            <!--       start loader       -->
                            <loader size="large" v-if="isLoader" />
                            <!--       end loader       -->

                            <table
                                class="table table-borderless table-hover table-centered m-0"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 0">
                                            <div
                                                class="form-check custom-control"
                                            >
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    v-model="isCheckAll"
                                                    style="
                                                        width: 17px;
                                                        height: 17px;
                                                    "
                                                />
                                            </div>
                                        </th>
                                        <th v-if="setting.name">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t("general.Name")
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            modules.sort(
                                                                sortString(
                                                                    'name'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            modules.sort(
                                                                sortString(
                                                                    '-name'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.name_e">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t("general.Name_en")
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            modules.sort(
                                                                sortString(
                                                                    'name_e'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            modules.sort(
                                                                sortString(
                                                                    '-name_e'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.is_active">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t("general.Status")
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            modules.sort(
                                                                sortString(
                                                                    'name_e'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            modules.sort(
                                                                sortString(
                                                                    '-name_e'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th>
                                            {{ $t("general.Action") }}
                                        </th>
                                        <th>
                                            <i class="fas fa-ellipsis-v"></i>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody v-if="modules.length > 0">
                                    <tr
                                        @click.capture="checkRow(data.id)"
                                        @dblclick.prevent="
                                            $bvModal.show(
                                                `modal-edit-${data.id}`
                                            )
                                        "
                                        v-for="(data, index) in modules"
                                        :key="data.id"
                                        class="body-tr-custom"
                                    >
                                        <td>
                                            <div
                                                class="form-check custom-control"
                                                style="min-height: 1.9em"
                                            >
                                                <input
                                                    style="
                                                        width: 17px;
                                                        height: 17px;
                                                    "
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    :value="data.id"
                                                    v-model="checkAll"
                                                />
                                            </div>
                                        </td>
                                        <td v-if="setting.name">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.name }}
                                            </h5>
                                        </td>
                                        <td v-if="setting.name_e">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.name_e }}
                                            </h5>
                                        </td>
                                        <td v-if="setting.is_active">
                                            <span
                                                :class="[
                                                    data.is_active == 'active'
                                                        ? 'text-success'
                                                        : 'text-danger',
                                                    'badge',
                                                ]"
                                            >
                                                {{
                                                    data.is_active == "active"
                                                        ? `${$t(
                                                              "general.Active"
                                                          )}`
                                                        : `${$t(
                                                              "general.Inactive"
                                                          )}`
                                                }}
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
                                                    {{ $t("general.commands") }}
                                                    <i
                                                        class="fas fa-angle-down"
                                                    ></i>
                                                </button>
                                                <div
                                                    class="dropdown-menu dropdown-menu-custom"
                                                >
                                                    <a
                                                        class="dropdown-item"
                                                        href="#"
                                                        @click="
                                                            $bvModal.show(
                                                                `modal-edit-${data.id}`
                                                            )
                                                        "
                                                    >
                                                        <div
                                                            class="d-flex justify-content-between align-items-center text-black"
                                                        >
                                                            <span>{{
                                                                $t(
                                                                    "general.edit"
                                                                )
                                                            }}</span>
                                                            <i
                                                                class="mdi mdi-square-edit-outline text-info"
                                                            ></i>
                                                        </div>
                                                    </a>
                                                    <a
                                                        class="dropdown-item text-black"
                                                        href="#"
                                                        @click.prevent="
                                                            deleteModule(
                                                                data.id
                                                            )
                                                        "
                                                    >
                                                        <div
                                                            class="d-flex justify-content-between align-items-center text-black"
                                                        >
                                                            <span>{{
                                                                $t(
                                                                    "general.delete"
                                                                )
                                                            }}</span>
                                                            <i
                                                                class="fas fa-times text-danger"
                                                            ></i>
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>

                                            <!--  edit   -->
                                            <b-modal
                                                :id="`modal-edit-${data.id}`"
                                                :title="$t('module.editmodule')"
                                                title-class="font-18"
                                                body-class="p-4"
                                                size="lg"
                                                :ref="`edit-${data.id}`"
                                                :hide-footer="true"
                                                @show="resetModalEdit(data.id)"
                                                @hidden="
                                                    resetModalHiddenEdit(
                                                        data.id
                                                    )
                                                "
                                            >
                                                <form>
                                                    <div
                                                        class="mb-3 d-flex justify-content-end"
                                                    >
                                                        <!-- Emulate built in modal footer ok and cancel button actions -->
                                                        <b-button
                                                            variant="success"
                                                            type="submit"
                                                            class="mx-1"
                                                            v-if="!isLoader"
                                                            @click.prevent="
                                                                editSubmit(
                                                                    data.id
                                                                )
                                                            "
                                                        >
                                                            {{
                                                                $t(
                                                                    "general.Edit"
                                                                )
                                                            }}
                                                        </b-button>

                                                        <b-button
                                                            variant="success"
                                                            class="mx-1"
                                                            disabled
                                                            v-else
                                                        >
                                                            <b-spinner
                                                                small
                                                            ></b-spinner>
                                                            <span
                                                                class="sr-only"
                                                                >{{
                                                                    $t(
                                                                        "login.Loading"
                                                                    )
                                                                }}...</span
                                                            >
                                                        </b-button>

                                                        <b-button
                                                            variant="danger"
                                                            type="button"
                                                            @click.prevent="
                                                                $bvModal.hide(
                                                                    `modal-edit-${data.id}`
                                                                )
                                                            "
                                                        >
                                                            {{
                                                                $t(
                                                                    "general.Cancel"
                                                                )
                                                            }}
                                                        </b-button>
                                                    </div>
                                                    <div class="row">
                                                        <div
                                                            class="col-8"
                                                            :class="
                                                                $i18n.locale ==
                                                                'ar'
                                                                    ? 'rtl'
                                                                    : 'ltr'
                                                            "
                                                        >
                                                            <ul id="myUL">
                                                                <li
                                                                    v-for="node in rootNodes"
                                                                    :key="
                                                                        node.id
                                                                    "
                                                                >
                                                                    <span>
                                                                        <i
                                                                            @click="
                                                                                getFirstLevelChildNodes(
                                                                                    node
                                                                                )
                                                                            "
                                                                            v-if="
                                                                                node.haveChildren
                                                                            "
                                                                            :class="
                                                                                node.collapsed
                                                                                    ? 'fa fa-caret-down'
                                                                                    : $i18n.locale ==
                                                                                      'ar'
                                                                                    ? 'fa fa-caret-left'
                                                                                    : 'fa fa-caret-right'
                                                                            "
                                                                        ></i>
                                                                        <span
                                                                            @click="
                                                                                setUpdateParentId(
                                                                                    node
                                                                                )
                                                                            "
                                                                            :class="{
                                                                                'without-children':
                                                                                    !node.haveChildren,
                                                                                active:
                                                                                    node.id ==
                                                                                    edit.parent_id,
                                                                            }"
                                                                        >
                                                                            {{
                                                                                $i18n.locale ==
                                                                                "ar"
                                                                                    ? node.name
                                                                                    : node.name_e
                                                                            }}
                                                                        </span>
                                                                    </span>
                                                                    <ul
                                                                        v-if="
                                                                            node.children &&
                                                                            node
                                                                                .children
                                                                                .length
                                                                        "
                                                                        class="nested"
                                                                    >
                                                                        <li
                                                                            v-for="childNode in node.children"
                                                                            :key="
                                                                                childNode.id
                                                                            "
                                                                        >
                                                                            <span>
                                                                                <i
                                                                                    @click="
                                                                                        getSecondLevelChildNodes(
                                                                                            node,
                                                                                            childNode
                                                                                        )
                                                                                    "
                                                                                    v-if="
                                                                                        childNode.haveChildren
                                                                                    "
                                                                                    :class="
                                                                                        childNode.collapsed
                                                                                            ? 'fa fa-caret-down'
                                                                                            : $i18n.locale ==
                                                                                              'ar'
                                                                                            ? 'fa fa-caret-left'
                                                                                            : 'fa fa-caret-right'
                                                                                    "
                                                                                >
                                                                                </i>
                                                                                <span
                                                                                    @click="
                                                                                        setUpdateParentId(
                                                                                            childNode
                                                                                        )
                                                                                    "
                                                                                    :class="{
                                                                                        'without-children':
                                                                                            !childNode.haveChildren,
                                                                                        active:
                                                                                            childNode.id ==
                                                                                            edit.parent_id,
                                                                                    }"
                                                                                >
                                                                                    {{
                                                                                        $i18n.locale ==
                                                                                        "ar"
                                                                                            ? childNode.name
                                                                                            : childNode.name_e
                                                                                    }}
                                                                                </span>
                                                                            </span>
                                                                            <ul
                                                                                v-if="
                                                                                    childNode.children &&
                                                                                    childNode
                                                                                        .children
                                                                                        .length
                                                                                "
                                                                                class="nested"
                                                                            >
                                                                                <li
                                                                                    v-for="child in childNode.children"
                                                                                    :key="
                                                                                        child.id
                                                                                    "
                                                                                >
                                                                                    <span>
                                                                                        <span
                                                                                            @click="
                                                                                                setUpdateParentId(
                                                                                                    child
                                                                                                )
                                                                                            "
                                                                                            :class="{
                                                                                                active:
                                                                                                    child.id ==
                                                                                                    edit.parent_id,
                                                                                            }"
                                                                                        >
                                                                                            {{
                                                                                                $i18n.locale ==
                                                                                                "ar"
                                                                                                    ? child.name
                                                                                                    : child.name_e
                                                                                            }}
                                                                                        </span>
                                                                                    </span>
                                                                                </li>
                                                                            </ul>
                                                                        </li>
                                                                    </ul>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="row">
                                                                <div
                                                                    class="col-12 direction"
                                                                    dir="rtl"
                                                                >
                                                                    <div
                                                                        class="form-group"
                                                                    >
                                                                        <label
                                                                            for="field-u-1"
                                                                            class="control-label"
                                                                        >
                                                                            {{
                                                                                $t(
                                                                                    "general.Name"
                                                                                )
                                                                            }}
                                                                            <span
                                                                                class="text-danger"
                                                                                >*</span
                                                                            >
                                                                        </label>
                                                                        <input
                                                                            type="text"
                                                                            class="form-control arabicInput"
                                                                            v-model="
                                                                                $v
                                                                                    .edit
                                                                                    .name
                                                                                    .$model
                                                                            "
                                                                            :class="{
                                                                                'is-invalid':
                                                                                    $v
                                                                                        .edit
                                                                                        .name
                                                                                        .$error ||
                                                                                    errors.name,
                                                                                'is-valid':
                                                                                    !$v
                                                                                        .edit
                                                                                        .name
                                                                                        .$invalid &&
                                                                                    !errors.name,
                                                                            }"
                                                                            :placeholder="
                                                                                $t(
                                                                                    'general.Name'
                                                                                )
                                                                            "
                                                                            id="field-u-1"
                                                                        />
                                                                        <div
                                                                            v-if="
                                                                                !$v
                                                                                    .edit
                                                                                    .name
                                                                                    .minLength
                                                                            "
                                                                            class="invalid-feedback"
                                                                        >
                                                                            {{
                                                                                $t(
                                                                                    "general.Itmustbeatleast"
                                                                                )
                                                                            }}
                                                                            {{
                                                                                $v
                                                                                    .edit
                                                                                    .name
                                                                                    .$params
                                                                                    .minLength
                                                                                    .min
                                                                            }}
                                                                            {{
                                                                                $t(
                                                                                    "general.letters"
                                                                                )
                                                                            }}
                                                                        </div>
                                                                        <div
                                                                            v-if="
                                                                                !$v
                                                                                    .edit
                                                                                    .name
                                                                                    .maxLength
                                                                            "
                                                                            class="invalid-feedback"
                                                                        >
                                                                            {{
                                                                                $t(
                                                                                    "general.Itmustbeatmost"
                                                                                )
                                                                            }}
                                                                            {{
                                                                                $v
                                                                                    .edit
                                                                                    .name
                                                                                    .$params
                                                                                    .maxLength
                                                                                    .max
                                                                            }}
                                                                            {{
                                                                                $t(
                                                                                    "general.letters"
                                                                                )
                                                                            }}
                                                                        </div>
                                                                        <template
                                                                            v-if="
                                                                                errors.name
                                                                            "
                                                                        >
                                                                            <ErrorMessage
                                                                                v-for="(
                                                                                    errorMessage,
                                                                                    index
                                                                                ) in errors.name"
                                                                                :key="
                                                                                    index
                                                                                "
                                                                                >{{
                                                                                    $t(
                                                                                        errorMessage
                                                                                    )
                                                                                }}</ErrorMessage
                                                                            >
                                                                        </template>
                                                                    </div>
                                                                </div>
                                                                <div
                                                                    class="col-12 direction-ltr"
                                                                    dir="ltr"
                                                                >
                                                                    <div
                                                                        class="form-group"
                                                                    >
                                                                        <label
                                                                            for="field-u-2"
                                                                            class="control-label"
                                                                        >
                                                                            {{
                                                                                $t(
                                                                                    "general.Name_en"
                                                                                )
                                                                            }}
                                                                            <span
                                                                                class="text-danger"
                                                                                >*</span
                                                                            >
                                                                        </label>
                                                                        <input
                                                                            type="text"
                                                                            class="form-control englishInput"
                                                                            v-model="
                                                                                $v
                                                                                    .edit
                                                                                    .name_e
                                                                                    .$model
                                                                            "
                                                                            :class="{
                                                                                'is-invalid':
                                                                                    $v
                                                                                        .edit
                                                                                        .name_e
                                                                                        .$error ||
                                                                                    errors.name_e,
                                                                                'is-valid':
                                                                                    !$v
                                                                                        .edit
                                                                                        .name_e
                                                                                        .$invalid &&
                                                                                    !errors.name_e,
                                                                            }"
                                                                            :placeholder="
                                                                                $t(
                                                                                    'general.Name_en'
                                                                                )
                                                                            "
                                                                            id="field-u-2"
                                                                        />
                                                                        <div
                                                                            v-if="
                                                                                !$v
                                                                                    .edit
                                                                                    .name_e
                                                                                    .minLength
                                                                            "
                                                                            class="invalid-feedback"
                                                                        >
                                                                            {{
                                                                                $t(
                                                                                    "general.Itmustbeatleast"
                                                                                )
                                                                            }}
                                                                            {{
                                                                                $v
                                                                                    .edit
                                                                                    .name_e
                                                                                    .$params
                                                                                    .minLength
                                                                                    .min
                                                                            }}
                                                                            {{
                                                                                $t(
                                                                                    "general.letters"
                                                                                )
                                                                            }}
                                                                        </div>
                                                                        <div
                                                                            v-if="
                                                                                !$v
                                                                                    .edit
                                                                                    .name_e
                                                                                    .maxLength
                                                                            "
                                                                            class="invalid-feedback"
                                                                        >
                                                                            {{
                                                                                $t(
                                                                                    "general.Itmustbeatmost"
                                                                                )
                                                                            }}
                                                                            {{
                                                                                $v
                                                                                    .edit
                                                                                    .name_e
                                                                                    .$params
                                                                                    .maxLength
                                                                                    .max
                                                                            }}
                                                                            {{
                                                                                $t(
                                                                                    "general.letters"
                                                                                )
                                                                            }}
                                                                        </div>
                                                                        <template
                                                                            v-if="
                                                                                errors.name_e
                                                                            "
                                                                        >
                                                                            <ErrorMessage
                                                                                v-for="(
                                                                                    errorMessage,
                                                                                    index
                                                                                ) in errors.name_e"
                                                                                :key="
                                                                                    index
                                                                                "
                                                                                >{{
                                                                                    $t(
                                                                                        errorMessage
                                                                                    )
                                                                                }}</ErrorMessage
                                                                            >
                                                                        </template>
                                                                    </div>
                                                                </div>
                                                                <div
                                                                    class="col-12"
                                                                >
                                                                    <div
                                                                        class="form-group"
                                                                    >
                                                                        <label
                                                                            class="mr-2 mb-2"
                                                                        >
                                                                            {{
                                                                                $t(
                                                                                    "general.Status"
                                                                                )
                                                                            }}
                                                                            <span
                                                                                class="text-danger"
                                                                                >*</span
                                                                            >
                                                                        </label>
                                                                        <b-form-group
                                                                            :class="{
                                                                                'is-invalid':
                                                                                    $v
                                                                                        .edit
                                                                                        .is_active
                                                                                        .$error ||
                                                                                    errors.is_active,
                                                                                'is-valid':
                                                                                    !$v
                                                                                        .edit
                                                                                        .is_active
                                                                                        .$invalid &&
                                                                                    !errors.is_active,
                                                                            }"
                                                                        >
                                                                            <b-form-radio
                                                                                class="d-inline-block"
                                                                                v-model="
                                                                                    $v
                                                                                        .edit
                                                                                        .is_active
                                                                                        .$model
                                                                                "
                                                                                name="some-radios"
                                                                                value="active"
                                                                                >{{
                                                                                    $t(
                                                                                        "general.Active"
                                                                                    )
                                                                                }}</b-form-radio
                                                                            >
                                                                            <b-form-radio
                                                                                class="d-inline-block m-1"
                                                                                v-model="
                                                                                    $v
                                                                                        .edit
                                                                                        .is_active
                                                                                        .$model
                                                                                "
                                                                                name="some-radios"
                                                                                value="inactive"
                                                                                >{{
                                                                                    $t(
                                                                                        "general.Inactive"
                                                                                    )
                                                                                }}</b-form-radio
                                                                            >
                                                                        </b-form-group>
                                                                        <template
                                                                            v-if="
                                                                                errors.is_active
                                                                            "
                                                                        >
                                                                            <ErrorMessage
                                                                                v-for="(
                                                                                    errorMessage,
                                                                                    index
                                                                                ) in errors.is_active"
                                                                                :key="
                                                                                    index
                                                                                "
                                                                                >{{
                                                                                    $t(
                                                                                        errorMessage
                                                                                    )
                                                                                }}
                                                                            </ErrorMessage>
                                                                        </template>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </b-modal>
                                            <!--  /edit   -->
                                        </td>
                                        <td>
                                            <i
                                                class="fe-info"
                                                style="font-size: 22px"
                                            ></i>
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <th class="text-center" colspan="6">
                                            {{ $t("general.notDataFound") }}
                                        </th>
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
<style scoped lang="scss">
ul,
#myUL {
    list-style-type: none;
}
#myUL {
    margin: 0;
    padding: 0;
    span {
        i {
            font-size: 20px;
            position: relative;
            top: 3px;
            color: black;
        }
        span:hover,
        i:hover {
            cursor: pointer;
        }
    }
}
.nested {
    display: block;
}
.active {
    color: #1abc9c;
}
.rtl {
    #myUL {
        .without-children {
            padding-right: 10px;
        }
        .nested {
            padding-right: 40px;
        }
    }
}
.ltr {
    #myUL {
        .without-children {
            padding-left: 10px;
        }
    }
}
/* Remove default bullets */
</style>
