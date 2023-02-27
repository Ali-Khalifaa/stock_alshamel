<script>
import Layout from "../../layouts/main";
import PageHeader from "../../../components/Page-header";
import adminApi from "../../../api/adminAxios";
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
import DatePicker from "vue2-datepicker";

/**
 * Advanced Table component
 */
export default {
    page: {
        title: "Closing Balance Edit",
        meta: [{ name: "description", content: "Closing Balance Edit" }],
    },
    components: {
        Layout,
        PageHeader,
        ErrorMessage,
        loader,
        Multiselect,
        DatePicker,
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
            search: "", //Search table column
            debounce: {},
            permId: "",
            pagination: {},

            stockTypeAr: [], //Fetch Parent Table Data
            dataAr: [], //Same Table Data
            enabled3: false,
            isLoader: false,

            edits: [
                {
                    stock_id: "",
                    date: "",
                    amount: "",
                },
            ],

            errors: {}, //Server Side Validation Errors
            isCheckAll: false,
            checkAll: [],
            is_disabled: false,
            current_page: 1,
        };
    },
    validations: {
        edits: {
            date: {
                required,
            },

            stock_id: { required, integer },
            amount: { required },
        },
    },
    watch: {
        /**
         * watch per_page
         */
        // per_page(after, befour) {
        //     this.getData();
        // },
        /**
         * watch search
         */
        // search(after, befour) {
        //     clearTimeout(this.debounce);
        //     this.debounce = setTimeout(() => {
        //         this.getData();
        //     }, 400);
        // },
        /**
         * watch check All table
         */
        // isCheckAll(after, befour) {
        //     if (after) {
        //         this.archDepartmentAr.forEach((el) => {
        //             if (!this.checkAll.includes(el.id)) {
        //                 this.checkAll.push(el.id);
        //             }
        //         });
        //     } else {
        //         this.checkAll = [];
        //     }
        // },
    },
    mounted() {
        let date = this.$router.currentRoute.query.date;
        this.getAllRow(date);
    },
    methods: {
        // getNet(edit, k) {
        //     if (edit.id == k) {
        //         edit.net = edit.qty * edit.price;
        //     }
        // },

        addNewRow() {
            this.edits.push({
                stock_id1: "",
                date1: "",
                amount1: "",
            });
        },

        isToday(someDate) {
            const today = new Date();
            someDate = new Date(someDate);
            return (
                someDate.getDate() == today.getDate() &&
                someDate.getMonth() == today.getMonth() &&
                someDate.getFullYear() == today.getFullYear()
            );
        },

        getAllRow(date) {
            this.getStock();
            adminApi
                .get(`/closing-balance/getentier/${date}`)
                .then((res) => {
                    let l = res.data;
                    this.edits = l.data;
                    // var data = this.edits;
                })
                .catch((err) => {
                    Swal.fire({
                        icon: "error",
                        title: `${this.$t("general.Error")}`,
                        text: `${this.$t("general.Thereisanerrorinthesystem")}`,
                    });
                });
        },
        back() {
            window.history.back();
        },

        /**
         *  edit Stock
         */
        editSubmit() {
            this.isLoader = true;
            adminApi
                .post(`/closing-balance/updaterow`, {
                    arrform: this.edits,
                })
                .then((res) => {
                    this.$router.push("closing-balance");
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
            // }
        },

        /**
         *  hidden Modal (edit)
         */
        resetModalHiddenEdit(id) {
            this.errors = {};
            this.edits = {
                stock_id1: "",
                date1: "",
                amount1: "",
            };
        },

        /**
         *  get parent
         */
        async getStock() {
            await adminApi
                .get(`/stock`)
                .then((res) => {
                    let l = res.data;
                    this.stockTypeAr = l.data;
                })
                .catch((err) => {
                    Swal.fire({
                        icon: "error",
                        title: `${this.$t("general.Error")}`,
                        text: `${this.$t("general.Thereisanerrorinthesystem")}`,
                    });
                });
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
                                {{ $t("ClosingBalance.EditClosingBalance") }}
                            </h4>
                        </div>
                        <div class="container">
                            <form>
                                <div class="mb-3 d-flex justify-content-end">
                                    <!-- Start Add New Record Button -->
                                    <!-- <b-button
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
                                    </b-button> -->
                                    <!-- End Add New Record Button -->
                                    <!-- Emulate built in modal footer ok and cancel button actions -->
                                    <template v-if="!is_disabled">
                                        <!-- Start Add Button -->
                                        <b-button
                                            variant="success"
                                            type="submit"
                                            class="mx-1"
                                            v-if="!isLoader"
                                            @click.prevent="editSubmit"
                                        >
                                            {{ $t("general.Edit") }}
                                        </b-button>
                                        <!-- End Add Button -->
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
                                    <!-- Start Cancel Button Modal -->
                                    <b-button
                                        variant="danger"
                                        type="button"
                                        @click.prevent="back"
                                    >
                                        {{ $t("general.Cancel") }}
                                    </b-button>
                                    <!-- End Cancel Button Modal -->
                                </div>
                                <div v-for="(edit, k) in edits" :key="k">
                                    <input
                                        type="hidden"
                                        v-model="edit.id"
                                        name="id[]"
                                    />
                                    <div
                                        class="row "
                                        v-if="k == 0"
                                    >
                                        <div class="col-md-4">
                                            <div
                                                class="form-group position-relative"
                                            >
                                                <label
                                                    class="control-label"
                                                    v-if="k == 0"
                                                >
                                                    {{
                                                        $t(
                                                            "ClosingBalance.date"
                                                        )
                                                    }}
                                                    <span class="text-danger"
                                                    >*</span
                                                    >
                                                </label>
                                                <date-picker
                                                    type="date"
                                                    v-model="edit.date"
                                                    format="YYYY-MM-DD"
                                                    valueType="format"
                                                    :confirm="false"
                                                    :disabled="
                                                        !isToday(edit.date)
                                                            ? true
                                                            : false
                                                    "
                                                ></date-picker>

                                                <!-- <div
                                                    v-if="
                                                        !$v.edit.date.required
                                                    "
                                                    class="invalid-feedback"
                                                >
                                                    {{
                                                        $t(
                                                            "general.fieldIsRequired"
                                                        )
                                                    }}
                                                </div> -->
                                                <!-- <template v-if="errors.date">
                                                    <ErrorMessage
                                                        v-for="(
                                                            errorMessage, index
                                                        ) in errors.date"
                                                        :key="index"
                                                        >{{
                                                            errorMessage
                                                        }}</ErrorMessage
                                                    >
                                                </template> -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div
                                                class="form-group position-relative"
                                            >
                                                <label
                                                    class="control-label"
                                                    v-if="k == 0"
                                                >
                                                    {{
                                                        $t(
                                                            "general.stockSector"
                                                        )
                                                    }}
                                                    <span class="text-danger"
                                                    >*</span
                                                    >
                                                </label>

                                                <multiselect
                                                    v-model="edit.stock_id"
                                                    :options="
                                                        stockTypeAr.map(
                                                            (type) => type.id
                                                        )
                                                    "
                                                    :custom-label="
                                                        (opt) =>
                                                            $i18n.locale
                                                                ? stockTypeAr.find(
                                                                      (x) =>
                                                                          x.id ==
                                                                          opt
                                                                  ).name
                                                                : stockTypeAr.find(
                                                                      (x) =>
                                                                          x.id ==
                                                                          opt
                                                                  ).name_e
                                                    "
                                                    :disabled="
                                                        !isToday(edit.date)
                                                            ? true
                                                            : false
                                                    "
                                                >
                                                </multiselect>

                                                <!-- <div
                                                    v-if="
                                                        !$v.edit.stock_id
                                                            .integer
                                                    "
                                                    class="invalid-feedback"
                                                >
                                                    {{
                                                        $t(
                                                            "general.fieldIsInteger"
                                                        )
                                                    }}
                                                </div> -->
                                                <!-- <template
                                                    v-if="errors.stock_id"
                                                >
                                                    <ErrorMessage
                                                        v-for="(
                                                            errorMessage, index
                                                        ) in errors.stock_id"
                                                        :key="index"
                                                        >{{
                                                            errorMessage
                                                        }}</ErrorMessage
                                                    >
                                                </template> -->
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label
                                                    for="field-2"
                                                    class="control-label"
                                                    v-if="k == 0"
                                                >
                                                    {{
                                                        $t(
                                                            "ClosingBalance.amount"
                                                        )
                                                    }}
                                                    <span class="text-danger"
                                                    >*</span
                                                    >
                                                </label>
                                                <input
                                                    type="number"
                                                    class="form-control"
                                                    v-model="edit.amount"
                                                    id="field-2"
                                                />

                                                <!-- <template v-if="errors.amount">
                                                    <ErrorMessage
                                                        v-for="(
                                                            errorMessage, index
                                                        ) in errors.amount"
                                                        :key="index"
                                                        >{{
                                                            errorMessage
                                                        }}</ErrorMessage
                                                    >
                                                </template> -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- start .table-responsive-->

                        <!-- end .table-responsive-->
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style>
.multiselect__single{
    font-size: 14px !important;
    font-weight: 500 !important;
}
</style>
