import Cookies from "js-cookie";
import adminApi from '../../api/adminAxios';
import router from "../../router/index";

// state
export const state = {
    token: Cookies.get("token") || null,
    // permission: JSON.parse(localStorage.getItem("permission")) || [],
    admin: JSON.parse(localStorage.getItem("admin")) || {},
}

// getters
export const getters = {
    token: state => state.token,
    permission: state => state.permission,
    loading: state => state.loading,
    admin: state => state.admin,
    errors: state => state.errors,
}

// mutations
export const mutations = {
    editToken(state,token){
        state.token = token;
        Cookies.set('token',token,{ expires: 7 });
    },
    // editPermission(state,permission){
    //
    //     let name = [];
    //     permission.forEach(el => {
    //         name.push(el.name);
    //         if(el.role && !name.includes(el.role)){
    //             name.push(el.role);
    //         }
    //     });
    //
    //     state.permission = name;
    //     localStorage.setItem('permission',JSON.stringify(name));
    // },
    editAdmin(state,admin){
        state.admin = admin;
        localStorage.setItem('admin',JSON.stringify(admin));
    },
    logoutToken(state){
        // state.roles = null;
        state.token = null;
        state.admin = null;
        // state.permission = null;
        // localStorage.removeItem('permission');
        localStorage.removeItem('admin');
        Cookies.remove('token')
    },
    editErrors(state,errors){
        state.errors = errors;
    }
};

// actions
export const actions = {};
