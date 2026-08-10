// window.stop_main_script = true;
if (typeof mouscripts !== "undefined") {
    now_app_version = mouscripts.apk_version();
    if (["2.9"].includes(now_app_version)) {
        window.stop_main_script = true;
        download_apk_confirm = confirm("يرجي تحميل احدث اصدار من التطبيق . \n اضغط Ok للذهاب الي رابط التحميل");
        if (download_apk_confirm) {
            mouscripts.open_external_link("https://new.elbatal-app.com");
            mouscripts.exitApp();
        } else {
            mouscripts.exitApp();
        }
    }
    // dev_id = mouscripts.getUniqueDeviceID();
    // if (dev_id == "cf5b-be36-14ca-0100") {
    // window.stop_main_script = true;
    // try {

    // link = getFolderPathUntil("project", mouscripts.get_index_link()) + "/files/plyr/plyr.html?data=dmlkX3RpdGxlPVNTQyUyNTIwMSZ2aWRfbGluaz1odHRwcyUyNTNBJTI1MkYlMjUyRnNzYy0xLWVuYy5lZGdlbmV4dGNkbi5uZXQlMjUyRm91dCUyNTJGdjElMjUyRmM2OTZlNDgxOWI1NTQxNDM4OGExYTQ4N2U4YTQ1Y2ExJTI1MkZpbmRleC5tcGQmdXNlcmFnZW50PSZoZWFkZXJzPSUyNTVCJTI1NUQmRHJtTGljZW5jZVVybD1kODRjMzI1ZjM2ODE0ZjM5YmJlNTkwODAyNzJiMTBjMyUyNTNBNTUwNzI3ZGU0Yzk2ZWYxZWNmZjg3NDkwNTQ5MzU4MGY=";

    // alert(link);
    // document.location.href = decodeURIComponent(link);
    // } catch (err) {
    //     alert('An error happened: ' + err.message);
    // }
    // }

}

// function getFolderPathUntil(folderName, cust_path = false) {
//     let path = cust_path != false ? cust_path : window.location.href; // Get the full file URL
//     let index = path.indexOf(`/${folderName}/`);

//     if (index !== -1) {
//         return path.substring(0, index + folderName.length + 1);
//     }

//     // If "project" is not found, remove the filename and return the folder path
//     return path.substring(0, path.lastIndexOf("/") + 1);
// }