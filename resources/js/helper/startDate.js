export  function formatDateTime(item){
    let now = new Date(item);
    let st = now.toISOString().match(/(\d{4}\-\d{2}\-\d{2})T(\d{2}:\d{2}:\d{2})/)
    return st[1]+' '+st[2];
};

export  function formatDateOnly(item){
    let now = new Date(item);
    let st = now.toISOString().match(/(\d{4}\-\d{2}\-\d{2})T(\d{2}:\d{2}:\d{2})/)
    return st[1];
};
