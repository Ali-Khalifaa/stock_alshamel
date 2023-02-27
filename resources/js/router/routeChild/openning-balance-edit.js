import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: "/openning-balance-edit",
        name: "openning-balance-edit",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../../views/pages/openning-balance/editform"),
    },
];
