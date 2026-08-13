obj = {
    "main_domain": "https://bs.cimanow.cc/",
    "server_domain": "https://cc.cimanow.online/",
    "working_state": true,
    "type": "cats",
    "server_name": "CimaNow",
    "server_title": "سيما ناو",
    "icon": `<i class="fas fa-film"></i>`,
    "notification_detected_tobics": ["movies"],
    "get_latest_domain": function () {
        server_title = mou_aflam_server["server_title"];
        server_domain = mou_aflam_server["server_domain"];
        $(".domain_checker .server_name").text(server_title)
        if (typeof local_servers_domains[server_requested] !== "undefined") {
            if (isOnlineUrl(local_servers_domains[server_requested])) {
                server_domain = local_servers_domains[server_requested];
            }
        }
        $.ajax({
            type: "HEAD",
            url: server_domain,
            timeout: 5000,
            success: function (data, textStatus, xhr) {
                domain_found(server_domain);
            },
            error: function (xhr, textStatus, errorThrown) {
                mou_aflam_server["get_prop_domains"](function (domains) {
                    window.now_domains_length = domains.length;
                    for (i = 0; i < window.now_domains_length; i++) {
                        var domain = domains[i];
                        window["get_prop_domain_" + i] = $.ajax({
                            "type": "GET",
                            "url": domain,
                            success: function (domain_res) {
                                doc = new DOMParser().parseFromString(domain_res, "text/html");
                                this_domain_full_url = this.url;
                                active_domain = new URL(this_domain_full_url);
                                active_domain = active_domain.protocol + "//" + active_domain.hostname;
                                if (domain_res.includes("adilbo_HTML_encoder") || $(doc).find("head title").text().trim() == "سيما ناو | عندما يكون للمشاهدة متعة خاصة") {
                                    domain_found(active_domain);
                                    for (let e = 0; e < window.now_domains_length; e++) {
                                        window["get_prop_domain_" + e].abort();
                                    }
                                }

                            }, error: function (xhr, textStatus, errorThrown) {
                                // console.log(this.url + " => " + xhr.status);
                            }
                        });
                    }
                    domain_search_timeout_fun();

                });


            }
        });
    },
    "get_prop_domains": function (callback) {
        prop_domains = [];
        $.ajax({
            type: "GET",
            url: what_window.elbatal_api + "panels/aflamservers/api.php",
            data: { "name": mou_aflam_server["server_name"] },
            success: function (res) {
                prop_domains.push(res.url);
                callback(prop_domains);
            }
        });
    },
    start_website: function () {

        if (getQueryVariable("film_url")) {
            qurey_data = get_Queries();
            mou_aflam_server.load_film_function(qurey_data);
        } else {
            $(".header").show();
            $(".server_content").show();
            $(".closer_cats_container hr").hide();
            $("#cats_container").hide();

            $("#custom_selectors").append(`<button class="custom_cat" data-url="الاحدث/">احدث الاضافات</button>`);
            $("#custom_selectors").append(`<button class="custom_cat" data-url="category/رمضان/رمضان-2025/">رمضان 2025</button>`);
            $("#custom_selectors").append(`<button class="custom_cat" data-url="/الاكثر-مشاهدة">الأكثر مشاهدة</button>`);
            $("#custom_selectors").append(`<button class="custom_cat" data-url="category/مسلسلات-عربية/">مسلسلات عربية</button>`);
            $("#custom_selectors").append(`<button class="custom_cat" data-url="category/مسلسلات-اجنبية/">مسلسلات اجنبية</button>`);
            $("#custom_selectors").append(`<button class="custom_cat" data-url="category/مسلسلات-تركية/">مسلسلات تركية</button>`);
            $("#custom_selectors").append(`<button class="custom_cat" data-url="category/افلام-عربية/">افلام عربية</button>`);
            $("#custom_selectors").append(`<button class="custom_cat" data-url="category/افلام-اجنبية/">افلام اجنبية</button>`);
            $("#custom_selectors").append(`<button class="custom_cat" data-url="category/افلام-تركية/">افلام تركية</button>`);
            $("#custom_selectors").append(`<button class="custom_cat" data-url="category/افلام-هندية/">افلام هندية</button>`);
            $("#custom_selectors").append(`<button class="custom_cat" data-url="category/البرامج-التلفزيونية/">البرامج التلفزيونية</button>`);
            $("#custom_selectors").append(`<button class="custom_cat" data-url="category/مسرحيات/">مسرحيات</button>`);

            $(document).on("click", ".custom_cat", function () {
                if (!$(this).hasClass("disabled")) {
                    $(".posts_ul").html("");
                    $(".custom_cat").addClass("disabled");
                    $(this).addClass("loading_elemnt");
                    $(this).removeClass("disabled");

                    $("#load_more_posts_btn").html(`<i class="fad fa-spinner-third fa-spin"></i> جاري التحميل`);


                    this_cat_url = $(this).attr("data-url");
                    this_cat_btn = $(this);
                    $.ajax({
                        "type": "GET",
                        "url": server_domain + this_cat_url,
                        success: function (res) {
                            doc = new DOMParser().parseFromString(res, "text/html");
                            $(".custom_cat").removeClass("disabled").removeClass("active");
                            $(this_cat_btn).addClass("active");
                            $(".custom_cat.loading_elemnt").removeClass("loading_elemnt");
                            mou_aflam_server.load_list_function(res, "first_load");
                        }
                    });

                }
            });
            // $("#load_more_posts_btn").attr("onclick", `mou_aflam_server.get_list_function('movies',this)`).click();

            $("#custom_selectors .custom_cat").eq(0).click();
        }
    }, get_list_function: function (page_url, this_btn) {
        page_url = isValidUrl(page_url) ? page_url : server_domain + page_url;
        disable_attr = $(this_btn).attr('disabled');
        if (loading_more_posts == false) {
            if (!(typeof disable_attr !== 'undefined' && disable_attr !== false)) {
                loading_more_posts = true;
                $(this_btn).html(`<i class="fad fa-spinner-third fa-spin"></i> جاري التحميل`);

                $.ajax({
                    "type": "GET",
                    "url": page_url,
                    success: function (res) {
                        mou_aflam_server.load_list_function(res, "load_more");
                        loading_more_posts = false;

                        $(this_btn).html(`تحميل المزيد`);
                    }
                });
            }

        }
    },
    "load_list_function": function (res, load_type = "first_load") {
        res = mou_aflam_server.get_cima_now_res(res);

        aflam_json = {};
        aflam_posts = [];
        doc = new DOMParser().parseFromString(res, "text/html");

        $(doc).find("section[aria-label='posts']").find("article[aria-label='post']").each(function () {
            film = {};
            $(this).find("li[aria-label='title']").find("em").remove();
            film.url = $(this).find("a[href]").attr("href");
            film.type = $(this).find(`[aria-label="episode"]`).length > 0 || film.url.includes("/selary/") ? "muslsal" : "film";
            film.title = $(this).find("li[aria-label='title']").text().trim();
            film.img = $(this).find("img").attr("data-src");
            if ($(this).find(`[aria-label="episode"]`).length > 0) {
                $(this).find(`[aria-label="episode"] em`).remove();
                film.eposide = parseInt($(this).find(`[aria-label="episode"]`).text().trim().match(/(\d+)/)[0], 10);
            }

            aflam_posts.push(film);
        });
        aflam_json.aflam = aflam_posts;
        next_button = $(doc).find(`ul[aria-label="pagination"] li`).eq(($(doc).find(`ul[aria-label="pagination"] li.active`).index() + 1));
        if (next_button.length > 0) {
            next_page_link = $(next_button).find("a[href]").attr("href");
            aflam_json.next_page = next_page_link;
        }

        load_aflam_posts(aflam_json, load_type);
    }, search_function: function (key) {
        search_url = "?s=" + key;
        $("#load_more_posts_btn").attr("onclick", `mou_aflam_server.get_list_function('${search_url}',this)`).removeAttr("disabled").show().click();
    },
    load_film_function: function (film) {
        film_title = decodeURIComponent(decodeURIComponent(decodeURIComponent(film.film_title)));
        film_url = decodeURIComponent(decodeURIComponent(decodeURIComponent(decodeURIComponent(film.film_url))));
        film_url = isValidUrl(film_url) ? film_url : server_domain + film_url;
        film_img = decodeURIComponent(film.film_img);

        page_type = film.film_type;
        film_eposide = film.eposide;
        $.ajax({
            "type": "GET",
            "url": film_url,
            success: function (res) {
                res = mou_aflam_server.get_cima_now_res(res);

                film_data = {};
                film_trs = {};
                doc = new DOMParser().parseFromString(res, "text/html");
                film_data.title = film_title;
                film_data.film_type = page_type;


                if (film_img == false || film_img == "" || !isValidUrl(film_img)) {
                    film_data.img = $(doc).find(`meta[property="og:image"]`).attr("content");
                } else {
                    film_data.img = film_img;
                }
                film_data.description = $(doc).find(`section[aria-label="details"]`).find(".tabcontent#details li").eq(0).find("p").text().trim();

                $(doc).find(`section[aria-label="details"]`).find(".tabcontent#details li").eq(0).remove();
                $(doc).find(`section[aria-label="details"]`).find(".tabcontent#details li").each(function () {
                    tr = {};
                    tr_key = $(this).find("strong").text().replace(":", "").trim();
                    $(this).find("strong").remove();
                    tr_val = $(this).text().trim();
                    film_trs[tr_key] = tr_val;
                })
                film_data.trs = film_trs;

                if ($(doc).find(`section[aria-label="details"]`).find(".tabcontent.trailer iframe").length > 0) {
                    film_data.trailer = $(doc).find(`section[aria-label="details"]`).find(".tabcontent.trailer iframe").attr("src");

                }



                show_film_data(film_data);
                if (page_type == "film") {
                    mou_aflam_server.load_msadr_watch(film_url, "film");
                } else if (page_type == "muslsal") {

                    $("#moasm_elmoslsal_container").show();
                    moasm_num = $(doc).find(`section[aria-label="seasons"] ul li`).length;
                    $("#moasm_num").text(` ( ${moasm_num} ) `);


                    // if (film_eposide !== "" && typeof film_eposide !== "undefined") {

                    // $(`#moasm_elmoslsal .mou_eps_num[data-7alkat_link="${active_mosem_link}"]`).click();
                    if (getQueryVariable("halka_num") !== false) {
                        const preload_halka_num = getQueryVariable("halka_num");
                        check_7alakat_loded = setInterval(function () {
                            if ($("#hlakat_elmoslsal .mou_eps_num").length > 0) {
                                // alert(preload_halka_num);

                                $("#hlakat_elmoslsal .mou_eps_num").each(function () {
                                    if ($(this).find("em").text() == preload_halka_num) {
                                        $(this).click();
                                    }
                                });
                                clearInterval(check_7alakat_loded);
                            }
                        }, 100);
                    }

                    // check_7alakat_loded = setInterval(function () {
                    //     if ($("#hlakat_elmoslsal .mou_eps_num").length > 0) {
                    //         $("#hlakat_elmoslsal .mou_eps_num").each(function () {
                    //             if ($(this).find("em").text() == film_eposide) {
                    //                 $(this).click();
                    //             }
                    //         });
                    //         clearInterval(check_7alakat_loded);
                    //     }
                    // }, 100);
                    // }


                    $(doc).find(`section[aria-label="seasons"] ul li`).each(function () {
                        $(this).find("a em").remove();
                        mosem_num = $(this).find("a").text().trim().match(/(\d+)/)[0];
                        epo_link = $(this).find("a").attr("href");
                        $("#moasm_elmoslsal").append(`<a class="mou_eps_num" data-7alkat_link="${epo_link}" onclick="mou_aflam_server.load_7alakat_function(this)" data-mosem_num="${mosem_num}"><em>${mosem_num}</em><span>موسم</span></a>`);

                        if ($(this).hasClass("active")) {
                            active_mosem_link = epo_link;
                        }
                    });
                    if (!$(`#moasm_elmoslsal .mou_eps_num[data-7alkat_link="${active_mosem_link}"]`).hasClass("activee")) {
                        $(`#moasm_elmoslsal .mou_eps_num[data-7alkat_link="${active_mosem_link}"]`).addClass("activee");
                    }
                    if (film_eposide !== "" && typeof film_eposide !== "undefined") {
                        $(`#moasm_elmoslsal .mou_eps_num[data-7alkat_link="${active_mosem_link}"]`).click();
                        check_7alakat_loded = setInterval(function () {
                            if ($("#hlakat_elmoslsal .mou_eps_num").length > 0) {
                                $("#hlakat_elmoslsal .mou_eps_num").each(function () {
                                    if ($(this).find("em").text() == film_eposide) {
                                        $(this).click();
                                    }
                                });
                                clearInterval(check_7alakat_loded);
                            }
                        }, 100);
                    }

                    // halkat_num = $(doc).find(`#episodes li`).length;
                    // $("#eposids_num").text(` ( ${halkat_num} ) `);
                    // console.log(halkat_num);
                    // $(doc).find(`#episodes li`).each(function () {
                    //     halka_num = parseInt($(this).find("a").text().trim().match(/(\d+)/)[0], 10);
                    //     epo_link = $(this).find("a").attr("href");
                    //     $("#hlakat_elmoslsal").append(`<a class="mou_eps_num" onclick="mou_aflam_server.load_msadr_watch('${epo_link}','muslsal',this)" data-halka_num="${halka_num}"><em>${halka_num}</em><span>حلقة</span></a>`);
                    // });

                    // $('#hlakat_elmoslsal').find('.mou_eps_num').sort(function (a, b) {
                    //     return $(b).find("em").text().trim() - $(a).find("em").text().trim();
                    // }).appendTo('#hlakat_elmoslsal');

                    $("#hlakat_elmoslsal_container").show();


                    if (getQueryVariable("mosem_num") !== false) {
                        mosem_num = getQueryVariable("mosem_num");
                        $(`[data-mosem_num="${mosem_num}"]`).click();
                    }
                    // if (getQueryVariable("halka_num") !== false) {
                    //     halka_num = getQueryVariable("halka_num");
                    //     $(`[data-halka_num="${halka_num}"]`).click();
                    // }
                }

            }
        });


    },
    load_7alakat_function: function (this_btn) {
        link = $(this_btn).attr("data-7alkat_link");
        $("#moasm_elmoslsal .mou_eps_num").removeClass("activee");
        $(this_btn).addClass("activee");
        // $("#hlakat_elmoslsal").html(`<span><i class="fas fa-circle-notch fa-spin fa-lg"></i> جاري التحميل</span>`);
        $("#hlakat_elmoslsal").html("");
        $("#eposids_num").html("");

        $.ajax({
            "type": "GET",
            "url": link,
            success: function (res) {
                res = mou_aflam_server.get_cima_now_res(res);
                doc = new DOMParser().parseFromString(res, "text/html");

                halkat_num = $(doc).find(`#eps li`).length;

                $("#eposids_num").text(` ( ${halkat_num} ) `);

                $(doc).find(`#eps li`).each(function () {
                    halka_num = parseInt($(this).find("em").text().trim().match(/(\d+)/)[0], 10);
                    epo_link = $(this).find("a").attr("href");

                    $("#hlakat_elmoslsal").append(`<a class="mou_eps_num" onclick="mou_aflam_server.load_msadr_watch('${epo_link}','muslsal',this)" data-halka_num="${halka_num}"><em>${halka_num}</em><span>حلقة</span></a>`);
                });
                $('#hlakat_elmoslsal').find('.mou_eps_num').sort(function (a, b) {
                    return $(b).find("em").text().trim() - $(a).find("em").text().trim();
                }).appendTo('#hlakat_elmoslsal');
            }
        })
    }, load_cimanow_watch_server: function (link, isDownload = false, this_btn = false) {

        this_halka_text = $("#hlakat_elmoslsal .mou_eps_num.activee ,#hlakat_elmoslsal .mou_eps_num.loading").length > 0 ? " الحلقة " + $("#hlakat_elmoslsal .mou_eps_num.activee ,#hlakat_elmoslsal .mou_eps_num.loading").find("em").text() : "";
        continue_watch_code = film_data.title + (film_data.film_type == "film" ? "" : this_halka_text);


        if (isValidUrl(link)) {

            link_ext = get_url_extension(link);
            if (link_ext == "mp4") {
                if (isDownload == false) {
                    full_title = $(this_btn).attr("data-full_title");
                    play_vid(src_link, `${full_title}`, `Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36`, `{'Referer':'${server_domain}'}`, continue_watch_code);
                } else {
                    add_for_downlaod(`downloads/`, full_title, false, src_link, `video`, `{'Referer':'${server_domain}'}`);
                }

            } else {
                $(this_btn).addClass("loading_elemnt");
                dummy_link = new URL(link);
                if (dummy_link.host == "vk.com") {
                    $.ajax({
                        "type": "GET",
                        "url": link,
                        success: function (res) {
                            doc = new DOMParser().parseFromString(res, "text/html");
                            $(this_btn).removeClass("loading_elemnt");

                            watch_link = $(doc).find(".docs_no_preview_download_btn_container").find("a[href]").attr("href");

                            if (isDownload == false) {
                                full_title = $(this_btn).attr("data-full_title");
                                play_vid(watch_link, `${full_title}`, `Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36`, `{'Referer':'${server_domain}'}`, continue_watch_code);
                            } else {
                                add_for_downlaod(`downloads/`, full_title, false, watch_link, `video`, `{'Referer':'${server_domain}'}`);
                            }

                        }
                    });


                } else {
                    console.log(link);
                }



            }

        }

    }, load_msadr_watch: function (link, watch_type, this_btn = false) {
        $(".watch_srces_btns").html("");
        $(".dl_srces_btns").html("");
        $(".loading_watch_srces").show();


        if (this_btn !== false) {
            $("#hlakat_elmoslsal .mou_eps_num").removeClass("activee");
            $(this_btn).addClass("activee");

            halka_num = $(this_btn).find("em").text().trim();
            mosem_num = $("#moasm_elmoslsal .mou_eps_num.activee").find("em").text().trim();
            now_query_data = get_Queries(2, "?" + query_data);
            now_query_data["mosem_num"] = mosem_num;
            now_query_data["halka_num"] = halka_num;
            query_data = encodeQueryData(now_query_data);

            $("#mou_watch_btn").click();
        }
        // if (this_btn !== false) {

        //     $(this_btn).addClass("activee");
        //     halka_num = $(this_btn).find("em").text().trim();
        //     now_query_data = get_Queries(2, "?" + query_data);
        //     now_query_data["halka_num"] = halka_num;
        //     query_data = encodeQueryData(now_query_data);
        // }

        this_halka_text = $("#hlakat_elmoslsal .mou_eps_num.activee ,#hlakat_elmoslsal .mou_eps_num.loading").length > 0 ? " الحلقة " + $("#hlakat_elmoslsal .mou_eps_num.activee ,#hlakat_elmoslsal .mou_eps_num.loading").find("em").text() : "";
        continue_watch_code = film_data.title + (film_data.film_type == "film" ? "" : this_halka_text);

        if (typeof loading_msadr_ajax !== "undefined" && loading_msadr_ajax !== false) {
            loading_msadr_ajax.abort();
        }

        watch_btn_link = $(doc).find("body > main > article a.shine").attr("href");

        console.log(watch_btn_link);

        mou_aflam_server.get_wathing_url(watch_btn_link, function (watching_url) {


            loading_msadr_ajax = $.ajax({
                "type": "GET",
                "url": decodeURIComponent(decodeURI(watching_url)),
                "headers": {
                    "User-Agent": what_window.Main_USER_AGENT,
                    "Referer": watch_btn_link
                },
                success: function (watching_res) {
                    watching_res = mou_aflam_server.get_cima_now_res(watching_res);
                    watching_doc = new DOMParser().parseFromString(watching_res, "text/html");


                    $(".loading_watch_srces").hide();

                    watching_domain = what_window.extractDomainWithProtocol(link + "watching/");

                    watching_page_url = decodeURIComponent(decodeURI(watching_url));
                    core_token = (watching_res.match(/var\s+tk\s*=\s*['"]([a-f0-9]{64})['"]/) || [])[1] || "";

                    $(watching_doc).find("#watch").find("[data-id]").each(function () {

                        server_data_id = $(this).attr("data-id");
                        server_data_index = $(this).attr("data-index");

                        watching_core = watching_domain + `/wp-content/themes/Cima%20Now%20New/core.php?token=${core_token}&action=switch&index=${server_data_index}&id=${server_data_id}`;

                        $.ajax({
                            "type": "GET",
                            "url": watching_core,
                            headers: {
                                "User-Agent": what_window.Main_USER_AGENT,
                                "Referer": encodeURI(watching_page_url),
                                "X-Requested-With": "XMLHttpRequest"
                            },
                            success: function (server_res) {
                                watching_source = $(server_res).attr("src");

                                mou_cust_server = what_window.is_in_mou_servers(watching_source);
                                if (mou_cust_server !== false) {

                                    add_to_title = page_type == "muslsal" ? " - حلقة " + $("#hlakat_elmoslsal .mou_eps_num.activee em").text() : "";
                                    full_title = film_data.title + add_to_title;

                                    continue_watch_code = film_data.title + (film_data.film_type == "film" ? "" : this_halka_text);

                                    $(`<span class="mou_btn watch_btn" data-full_title="${full_title}" data-continue_watch_code="${continue_watch_code}" onclick="what_window.play_dl_mou_cust_server('${mou_cust_server}','${full_title}','${watching_source}',true,this,\`{'Referer':'${mou_aflam_server.server_domain}'}\`)">${mou_cust_server}</span>`).appendTo(".watch_srces_btns");


                                    add_to_dls = true;
                                    if (typeof what_window.cust_servers[mou_cust_server]["dl_status"] !== "undefined" && what_window.cust_servers[mou_cust_server]["dl_status"] == false) {
                                        add_to_dls = false;
                                    }
                                    if (add_to_dls) {

                                        $(`<span class="mou_btn watch_btn" data-full_title="${full_title}" data-continue_watch_code="${continue_watch_code}" onclick="what_window.play_dl_mou_cust_server('${mou_cust_server}','${full_title}','${watching_source}',false,this,\`{'Referer':'${mou_aflam_server.server_domain}'}\`)">${mou_cust_server}</span>`).appendTo(".dl_srces_btns");
                                    }

                                    // what_window.cust_servers[mou_cust_server]["get_srcs"](this_server_url, function (srcs) {
                                    //     srcs.forEach(src => {

                                    //         mou_aflam_server.add_watch_dl_src(src);
                                    //     });
                                    // })
                                }


                            }
                        });


                    })


                }
            })
        });
    }, decode_page_smart: function (html) {
        if (typeof html !== "string" || !html) return null;

        // (أ) تنسيق blog-post: data-<عشوائي> + array (new Array أو []) + eval(atob) — مفتاح ذاتي التكيّف
        try {
            const v = html.match(/data-[a-z0-9]+\s*=\s*['"](\d+)['"]/);
            const l = html.match(/eval\s*\(\s*atob\s*\(\s*'([^']+)'\s*\)\s*\)/);
            if (v && l) {
                const ls = atob(l[1]);
                const c = ls.match(/\+\s*(\d{4,})\s*\+\s*(\d{4,})/);
                if (c) {
                    const key = String(parseInt(v[1], 10) + parseInt(c[1], 10) + parseInt(c[2], 10));
                    const b = html.match(/\b\w+\s*=\s*(?:new\s*Array\(\s*|\[)([\s\S]*?)(?:\)|\])\s*;[\s\S]*?eval/);
                    if (b) {
                        const chunks = [];
                        const re = /['"]([A-Za-z0-9+/=]+)['"]/g;
                        let m;
                        while ((m = re.exec(b[1]))) chunks.push(m[1]);
                        if (chunks.length > 0) {
                            const dec = atob(chunks.join(""));
                            const bytes = new Uint8Array(dec.length);
                            for (let i = 0; i < dec.length; i++) {
                                bytes[i] = dec.charCodeAt(i) ^ key.charCodeAt(i % key.length);
                            }
                            const out = new TextDecoder("utf-8").decode(bytes);
                            if (out.length > 500) return out;
                        }
                    }
                }
            }
        } catch (e) {}

        // (ب) البيانات كلها داخل eval(atob('...')) مباشرة
        try {
            const l = html.match(/eval\s*\(\s*atob\s*\(\s*'([A-Za-z0-9+/=]{300,})'\s*\)\s*\)/);
            if (l) {
                const out = atob(l[1]);
                if (out.length > 500 && (out.indexOf("<") !== -1 || out.indexOf("window.") !== -1 || out.indexOf("<html") !== -1)) return out;
            }
        } catch (e) {}

        // (ج) أطول تجميع لسلاسل base64 داخل الصفحة
        try {
            const chunks = [];
            const re = /['"]([A-Za-z0-9+/=]{200,})['"]/g;
            let m;
            while ((m = re.exec(html))) chunks.push(m[1]);
            if (chunks.length > 0) {
                const joined = chunks.join("");
                const out = atob(joined);
                if (out.length > 500 && (out.indexOf("<") !== -1 || out.indexOf("window.") !== -1 || out.indexOf("<html") !== -1)) return out;
            }
        } catch (e) {}

        return null;
    }, get_cima_now_res: function (res) {
        if (typeof res !== "string" || !res) return res;

        console.log("%c[CimaNow Debug] فحص كتل البيانات الضخمة المجمعة...", "color: cyan; font-weight: bold;");

        // 0. فك كل الطبقات المشفرة (blog-post وكل الأشكال) حتى نصيفها
        try {
            let guard = 0;
            while (guard < 3) {
                const dec = mou_aflam_server.decode_page_smart(res);
                if (!dec || dec === res) break;
                console.log("%c[CimaNow Debug] تم فك صفحة مشفرة بنجاح. الطول: " + dec.length, "color: white; background: green;");
                res = dec;
                guard++;
            }
        } catch (err) {
            console.error("[CimaNow Debug] فشل فك الصفحة المشفرة:", err);
        }

        try {
            let encodedData = "";
            let offset = 87653;

            // 1. استخراج أضخم نص مجمع بعلامة +
            // هذا الـ Regex يبحث عن السلاسل النصية التي تتبعها علامة + وتستمر لأسطر عديدة
            const megaPattern = /(['"][\w\d~\\/+=]+['"](?:\s*\+\s*['"][\w\d~\\/+=]+['"])+)/g;
            let matches = res.match(megaPattern);

            if (matches) {
                let longestMatch = "";
                matches.forEach(m => {
                    if (m.length > longestMatch.length) longestMatch = m;
                });

                // تنظيف الكتلة المكتشفة وتجميعها في نص واحد
                encodedData = longestMatch.replace(/['"]\s*\+\s*['"]/g, '').replace(/['"]/g, '').replace(/\s+/g, '');
                console.log("[CimaNow Debug] تم تجميع الكتلة الضخمة بنجاح. الطول: " + encodedData.length);
            }

            // 2. إذا فشل الحل الأول، نبحث عن المتغير المرتبط بـ split (الخطة ب)
            if (!encodedData) {
                const varMatch = res.match(/([\w\d_]+)\.split\(/);
                if (varMatch) {
                    const varName = varMatch[1];
                    const lines = res.split('\n');
                    lines.forEach(line => {
                        if (line.includes(varName) && (line.includes("'") || line.includes('"'))) {
                            let content = line.match(/['"]([^'"]+)['"]/);
                            if (content) encodedData += content[1];
                        }
                    });
                }
            }

            if (!encodedData || encodedData.length < 1000) {
                console.error("[CimaNow Debug] فشل استخراج البيانات الحقيقية.");
                return res;
            }

            // 3. استخراج الـ Offset (رقم الطرح)
            const offsetMatch = res.match(/_r\s*=\s*(\d+)/) || res.match(/-\s*(\d{5,6})/);
            if (offsetMatch) {
                offset = parseInt(offsetMatch[1]);
                console.log("[CimaNow Debug] الـ Offset النشط: " + offset);
            }

            // 4. فك التشفير الحسابي
            let decoded_html = '';
            let parts = encodedData.split('~');

            for (let i = 0; i < parts.length; i++) {
                let part = parts[i].trim();
                if (part) {
                    try {
                        let rawDecoded = atob(part);
                        let numValue = parseInt(rawDecoded.replace(/\D/g, ''));
                        if (!isNaN(numValue)) {
                            decoded_html += String.fromCharCode(numValue - offset);
                        }
                    } catch (e) { continue; }
                }
            }

            if (decoded_html.length > 500) {
                try {
                    let finalHtml = decodeURIComponent(escape(decoded_html));
                    console.log("%c[CimaNow Debug] SUCCESS! تم استرجاع الصفحة. الطول: " + finalHtml.length, "color: white; background: green;");
                    return finalHtml;
                } catch (e) {
                    return decoded_html;
                }
            } else {
                console.warn("[CimaNow Debug] النتيجة لا تزال صغيرة جداً.");
            }

        } catch (err) {
            console.error("[CimaNow Debug] خطأ:", err);
        }

        return res;
    }, get_wathing_url: function (watch_btn_link, callback) {

        loading_msadr_ajax = $.ajax({
            "type": "GET",
            "url": watch_btn_link,
            "headers": {
                "User-Agent": what_window.Main_USER_AGENT,
                "Referer": mou_aflam_server.server_domain
            },
            success: function (res, textStatus, xhr) {

                // محاولة استخراج الهيدر set-cookie
                var setCookie = xhr.getResponseHeader('Set-Cookie');
                mou_aflam_server.phpSessId = null;

                if (setCookie) {
                    // استخدام Regex لاستخراج قيمة PHPSESSID فقط
                    var match = setCookie.match(/PHPSESSID=([^;]+)/);
                    if (match) {
                        mou_aflam_server.phpSessId = match[1];
                        console.log("تم استخراج PHPSESSID بنجاح: " + mou_aflam_server.phpSessId);
                    }
                } else {
                    // في حال لم يسمح المتصفح بالوصول للهيدر مباشرة، نبحث في كل الهيدرز المتاحة
                    console.log("لم يتم العثور على Set-Cookie في الهيدرز المباشرة، جاري الفحص الشامل...");
                    var allHeaders = xhr.getAllResponseHeaders().toLowerCase();
                    var matchAll = allHeaders.match(/phpsessid=([^;|\s|\n]+)/);
                    if (matchAll) {
                        mou_aflam_server.phpSessId = matchAll[1];
                        console.log("تم العثور على القيمة في الهيدرز العامة: " + mou_aflam_server.phpSessId);
                    }
                }

                if (!mou_aflam_server.phpSessId) {
                    console.warn("تعذر الوصول لـ PHPSESSID. قد يكون السبب قيود CORS من المتصفح.");
                }

                loading_msadr_ajax = $.ajax({
                    "type": "GET",
                    "url": "https://rm.freex2line.online/2020/02/blog-post.html/",
                    "headers": {
                        "User-Agent": what_window.Main_USER_AGENT,
                        "Referer": watch_btn_link,
                        "Cookie": mou_aflam_server.phpSessId ? `PHPSESSID=${mou_aflam_server.phpSessId}` : "",
                        "sec-ch-ua": "\"Not(A:Brand\";v=\"8\", \"Chromium\";v=\"144\"",
                        "sec-ch-ua-mobile": "?0",
                        "sec-ch-ua-platform": "\"Windows\"",
                        "Sec-Fetch-Dest": "empty",
                        "Sec-Fetch-Mode": "cors",
                        "Sec-Fetch-Site": "cross-site",
                        "Priority": "u=1, i",
                        "Pragma": "no-cache",
                        "Cache-Control": "no-cache, no-store, must-revalidate, max-age=0",
                        "Accept": "*/*",
                        "Accept-Language": "en-US",
                        "Accept-Encoding": "gzip, deflate, br, zstd"
                    },
                    success: function (redrict_res, textStatus, xhr) {

                        mou_aflam_server.get_direct_watch_link(redrict_res, function (err, finalUrl) {
                            if (err) {
                                console.error("[✘] خطأ في عملية التشفير: " + err.message);
                            } else {
                                console.log("%c[✔] تم توليد الرابط بنجاح:", "color: #4caf50; font-size: 14px; font-weight: bold;");
                                console.info(finalUrl);

                                setTimeout(function () {

                                    var baseUrl = finalUrl.split("?")[0];
                                    var postBody = finalUrl.split("?")[1] || "";

                                    $.ajax({
                                        "type": "POST",
                                        "url": baseUrl,
                                        "headers": {
                                            "User-Agent": what_window.Main_USER_AGENT,
                                            "Referer": "https://rm.freex2line.online/2020/02/blog-post.html/",
                                            "Cookie": mou_aflam_server.phpSessId ? `PHPSESSID=${mou_aflam_server.phpSessId}` : "",
                                            "sec-ch-ua": "\"Not(A:Brand\";v=\"8\", \"Chromium\";v=\"144\"",
                                            "sec-ch-ua-mobile": "?0",
                                            "sec-ch-ua-platform": "\"Windows\"",
                                            "Sec-Fetch-Dest": "empty",
                                            "Sec-Fetch-Mode": "cors",
                                            "Sec-Fetch-Site": "cross-site",
                                            "Priority": "u=1, i",
                                            "Pragma": "no-cache",
                                            "Cache-Control": "no-cache, no-store, must-revalidate, max-age=0",
                                            "Accept": "*/*",
                                            "Accept-Language": "en-US",
                                            "Accept-Encoding": "gzip, deflate, br, zstd"
                                        },
                                        "contentType": "application/x-www-form-urlencoded",
                                        "data": postBody,
                                        success: function (redrict_res, textStatus, xhr) {
                                            callback(decodeURIComponent(decodeURI(redrict_res)));
                                        }
                                    });
                                }, 10 * 1000);

                            }
                        });

                    }
                });



            }
        });

    }, get_direct_watch_link: function (res, callback) {
        try {
            const extract = (regex) => {
                const match = res.match(regex);
                return match ? match[1] : "";
            };

            // 0. فك كل الطبقات المشفرة (blog-post وكل الأشكال) — لازم قبل استخراج ptr/map
            try {
                let guard = 0;
                while (guard < 3) {
                    const dec = mou_aflam_server.decode_page_smart(res);
                    if (!dec || dec === res) break;
                    console.log("%c[CimaNow Debug] get_direct_watch_link: تم فك الطبقة، الطول = " + dec.length, "color: cyan; font-weight: bold;");
                    res = dec;
                    guard++;
                }
            } catch (e) {}

            // 1. بنية ptr_/map_/ctx_
            const ptrKey = extract(/window\.(ptr_[a-z0-9]+)\s*=/);
            const mapKey = extract(/window\.(map_[a-z0-9]+)\s*=/);
            let ch = "", rid = "", encodedKey = "", salt = "", cKey = "", rKey = "", kKey = "", cfgS = "";

            if (ptrKey && mapKey) {
                const ptrValue = extract(new RegExp(`window\\.${ptrKey}\\s*=\\s*['"]([^'"]+)['"]`));
                const ctxBody = ptrValue ? extract(new RegExp(`window\\.?\\[?['"]?${ptrValue}['"]?\\]?\\s*=\\s*\\{([\\s\\S]*?)\\};`, "s")) : "";
                const mapBody = extract(new RegExp(`window\\.${mapKey}\\s*=\\s*\\{([\\s\\S]*?)\\};`, "s"));

                const vars = {};
                const varRe = /(['"]?)v_([0-9A-Za-z]+)\1\s*:\s*['"]([^'"]+)['"]/g;
                let vm;
                while ((vm = varRe.exec(ctxBody))) vars["v_" + vm[2]] = vm[3];

                const roleKey = {};
                const mg = /\b(ch|ri|ke|se)\b\s*:\s*['"]([^'"]+)['"]/g;
                let mm;
                while ((mm = mg.exec(mapBody))) roleKey[mm[1]] = mm[2];

                ch = vars[roleKey["ch"]] || "";
                rid = vars[roleKey["ri"]] || "";
                encodedKey = vars[roleKey["ke"]] || "";
                salt = vars[roleKey["se"]] || "";
            }

            // 2. بنية _0x_cfg + window.<name> (fallback)
            let cfgBody = extract(/window\._0x_cfg\s*=\s*\{(.*?)\};/s);
            if (cfgBody) {
                const config = {};
                const cg = /([crks])\s*:\s*['"]([^'"]+)['"]/g;
                let gm;
                while ((gm = cg.exec(cfgBody))) if (!(gm[1] in config)) config[gm[1]] = gm[2];
                cKey = config.c || "";
                rKey = config.r || "";
                kKey = config.k || "";
                cfgS = config.s || "";
            }
            if (!rid || !ch || !encodedKey || !salt) {
                const winVal = (name) => extract(res, new RegExp(`window\\.?\\[?['"]?${name}['"]?\\]?\\s*=\\s*['"]([^'"]+)['"]`));
                ch = ch || winVal(cKey);
                rid = rid || winVal(rKey);
                encodedKey = encodedKey || winVal(kKey);
                salt = salt || cfgS;
            }

            if (!rid || !ch || !encodedKey || !salt) {
                throw new Error("فشل العثور على هيكل البيانات الديناميكي (V6)");
            }

            // 3. فك تشفير المفتاح السري (XOR Logic)
            const decodeSecret = (str, s) => {
                let k = atob(str), resStr = "";
                for (let i = 0; i < k.length; i++) {
                    resStr += String.fromCharCode(k.charCodeAt(i) ^ s.charCodeAt(i % s.length));
                }
                return resStr;
            };
            const secretKey = decodeSecret(encodedKey, salt);

            // 4. إعداد التوقيع الرقمي (HMAC-SHA256)
            // على وإندرويد WebView اللي بيشتغل على http:// غير آمنة مكتبة WebCrypto
            // (crypto.subtle) مش متاحة — بنفضل crypto.subtle لو موجودة وبنوقع fallback
            // HMAC-SHA256 خالص في JS لو مش متاحة (بيشتغل على التطبيق والإلكترون).
            const encoder = new TextEncoder();
            const dataToSign = rid + ch + "TW96aWxsYS81LjIw";
            const fp = "TW96aWxsYS81LjIw";

            const pureJSB64Token = (function () {
                // تنفيذ SHA-256 كامل خالص في JS (مضبوط ومعتمد مقابل المنفذات المعيارية)
                const sha256hex = (ascii) => {
                    const K = [
                        0x428a2f98,0x71374491,0xb5c0fbcf,0xe9b5dba5,0x3956c25b,0x59f111f1,0x923f82a4,0xab1c5ed5,
                        0xd807aa98,0x12835b01,0x243185be,0x550c7dc3,0x72be5d74,0x80deb1fe,0x9bdc06a7,0xc19bf174,
                        0xe49b69c1,0xefbe4786,0x0fc19dc6,0x240ca1cc,0x2de92c6f,0x4a7484aa,0x5cb0a9dc,0x76f988da,
                        0x983e5152,0xa831c66d,0xb00327c8,0xbf597fc7,0xc6e00bf3,0xd5a79147,0x06ca6351,0x14292967,
                        0x27b70a85,0x2e1b2138,0x4d2c6dfc,0x53380d13,0x650a7354,0x766a0abb,0x81c2c92e,0x92722c85,
                        0xa2bfe8a1,0xa81a664b,0xc24b8b70,0xc76c51a3,0xd192e819,0xd6990624,0xf40e3585,0x106aa070,
                        0x19a4c116,0x1e376c08,0x2748774c,0x34b0bcb5,0x391c0cb3,0x4ed8aa4a,0x5b9cca4f,0x682e6ff3,
                        0x748f82ee,0x78a5636f,0x84c87814,0x8cc70208,0x90befffa,0xa4506ceb,0xbef9a3f7,0xc67178f2
                    ];
                    const rotr = (x, n) => (x >>> n) | (x << (32 - n));
                    const ch = (x, y, z) => (x & y) ^ (~x & z);
                    const maj = (x, y, z) => (x & y) ^ (x & z) ^ (y & z);
                    const bs = (arr, o) => (arr[o + 3] | (arr[o + 2] << 8) | (arr[o + 1] << 16) | (arr[o] << 24)) >>> 0;

                    const msg = strBytes2(ascii);
                    const bitlen = msg.length * 8;
                    const L = Math.ceil((msg.length + 1 + 8) / 64) * 64;
                    const bb = new Uint8Array(L);
                    bb.set(msg);
                    bb[msg.length] = 0x80;
                    const dv = new DataView(bb.buffer);
                    dv.setUint32(L - 4, bitlen >>> 0, false);
                    dv.setUint32(L - 8, Math.floor(bitlen / 4294967296), false);

                    let H0 = 0x6a09e667 | 0, H1 = 0xbb67ae85 | 0, H2 = 0x3c6ef372 | 0, H3 = 0xa54ff53a | 0,
                        H4 = 0x510e527f | 0, H5 = 0x9b05688c | 0, H6 = 0x1f83d9ab | 0, H7 = 0x5be0cd19 | 0;

                    for (let i = 0; i < L; i += 64) {
                        const w = new Array(64);
                        for (let t = 0; t < 16; t++) w[t] = bs(bb, i + t * 4);
                        for (let t = 16; t < 64; t++) {
                            const s0 = rotr(w[t - 15], 7) ^ rotr(w[t - 15], 18) ^ (w[t - 15] >>> 3);
                            const s1 = rotr(w[t - 2], 17) ^ rotr(w[t - 2], 19) ^ (w[t - 2] >>> 10);
                            w[t] = (w[t - 16] + s0 + w[t - 7] + s1) >>> 0;
                        }
                        let a0 = H0, b0 = H1, c0 = H2, d0 = H3, e0 = H4, f0 = H5, g0 = H6, h0 = H7;
                        for (let t = 0; t < 64; t++) {
                            const S1 = rotr(e0, 6) ^ rotr(e0, 11) ^ rotr(e0, 25);
                            const T1 = (h0 + S1 + ch(e0, f0, g0) + K[t] + w[t]) >>> 0;
                            const S0 = rotr(a0, 2) ^ rotr(a0, 13) ^ rotr(a0, 22);
                            const T2 = (S0 + maj(a0, b0, c0)) >>> 0;
                            h0 = g0; g0 = f0; f0 = e0; e0 = (d0 + T1) >>> 0; d0 = c0; c0 = b0; b0 = a0; a0 = (T1 + T2) >>> 0;
                        }
                        H0 = (H0 + a0) >>> 0; H1 = (H1 + b0) >>> 0; H2 = (H2 + c0) >>> 0; H3 = (H3 + d0) >>> 0;
                        H4 = (H4 + e0) >>> 0; H5 = (H5 + f0) >>> 0; H6 = (H6 + g0) >>> 0; H7 = (H7 + h0) >>> 0;
                    }
                    return [H0, H1, H2, H3, H4, H5, H6, H7].map(x => ("00000000" + (x >>> 0).toString(16)).slice(-8)).join("");
                };
                const strBytes2 = (s) => { const a = []; for (let i = 0; i < s.length; i++) a.push(s.charCodeAt(i) & 0xff); return a; };
                const hexBytes = (h) => { const a = []; for (let i = 0; i < h.length; i += 2) a.push(parseInt(h.substr(i, 2), 16)); return a; };
                const b64Chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
                const bytesToB64 = (bytes) => {
                    let out = "";
                    for (let i = 0; i < bytes.length; i += 3) {
                        const b0 = bytes[i], b1 = i + 1 < bytes.length ? bytes[i + 1] : 0, b2 = i + 2 < bytes.length ? bytes[i + 2] : 0;
                        out += b64Chars.charAt(b0 >> 2);
                        out += b64Chars.charAt(((b0 & 3) << 4) | (b1 >> 4));
                        out += i + 1 < bytes.length ? b64Chars.charAt(((b1 & 15) << 2) | (b2 >> 6)) : "=";
                        out += i + 2 < bytes.length ? b64Chars.charAt(b2 & 63) : "=";
                    }
                    return out;
                };
                const hmacSha256B64 = (key, msg) => {
                    let k = strBytes2(key);
                    if (k.length > 64) k = hexBytes(sha256hex(key));
                    const ipad = new Array(64).fill(0x36), opad = new Array(64).fill(0x5c);
                    for (let i = 0; i < k.length; i++) { ipad[i] ^= k[i]; opad[i] ^= k[i]; }
                    const inner = sha256hex(String.fromCharCode.apply(null, [].concat(ipad, strBytes2(msg))));
                    const outer = sha256hex(String.fromCharCode.apply(null, [].concat(opad, hexBytes(inner))));
                    return bytesToB64(hexBytes(outer));
                };
                return hmacSha256B64(secretKey, dataToSign);
            })();

            const cryptoLib = (window.crypto && window.crypto.subtle) || undefined;
            const finish = function (b64Token) {
                const finalUrl = `https://rm.freex2line.online/2020/02/blog-post.html/get-link.php?request_id=${encodeURIComponent(rid)}&hmac_token=${encodeURIComponent(b64Token)}&ch=${encodeURIComponent(ch)}&fp=${fp}`;
                console.log("%c[CimaNow Debug] [✔] تم توليد الرابط بنجاح: ", "color: #4caf50; font-weight: bold;", finalUrl);
                if (typeof callback === "function") callback(null, finalUrl);
            };

            if (!cryptoLib) {
                finish(pureJSB64Token);
            } else {
                cryptoLib.importKey(
                    "raw",
                    encoder.encode(secretKey),
                    { name: "HMAC", hash: "SHA-256" },
                    false,
                    ["sign"]
                )
                    .then((key) => cryptoLib.sign("HMAC", key, encoder.encode(dataToSign)))
                    .then((signature) => {
                        const hashArray = Array.from(new Uint8Array(signature));
                        finish(btoa(String.fromCharCode.apply(null, hashArray)));
                    })
                    .catch((err) => {
                        console.warn("[CimaNow Debug] crypto.subtle فشل، استخدام fallback JS خالص", err);
                        finish(pureJSB64Token);
                    });
            }

        } catch (err) {
            if (typeof callback === "function") callback(err, null);
        }
    }
    // get_direct_watch_link: function (res, callback) {
    //     try {
    //         const extract = (regex) => {
    //             const match = res.match(regex);
    //             return match ? match[1] : "";
    //         };
    //         const rid = extract(/window\.__requestId\s*=\s*['"]([^'"]+)['"]/);
    //         const ch = extract(/window\.__ch\s*=\s*['"]([^'"]+)['"]/);
    //         const m = parseInt(extract(/window\._0x_m\s*=\s*(\d+)/)) || 0;
    //         const r = parseInt(extract(/window\._0x_r\s*=\s*(\d+)/)) || 0;
    //         if (!rid || !ch) {
    //             throw new Error("تعذر استخراج rid أو ch من الاستجابة");
    //         }
    //         const secretKey = "[pW4X+6B4(pf&-dQ";
    //         const encoder = new TextEncoder();
    //         crypto.subtle.importKey(
    //             "raw",
    //             encoder.encode(secretKey),
    //             { name: "HMAC", hash: "SHA-256" },
    //             false,
    //             ["sign"]
    //         )
    //             .then(key => {
    //                 const fp = "TW96aWxsYTIwNzM2";
    //                 const dataToSign = encoder.encode(rid + ch + fp);

    //                 return crypto.subtle.sign("HMAC", key, dataToSign);
    //             })
    //             .then(signature => {
    //                 const hashArray = Array.from(new Uint8Array(signature));
    //                 const b64Token = btoa(String.fromCharCode.apply(null, hashArray));
    //                 const fp = "TW96aWxsYTIwNzM2";
    //                 const finalUrl = `https://rm.freex2line.online/2020/02/blog-post.html/get-link.php?request_id=${rid}&hmac_token=${encodeURIComponent(b64Token)}&ch=${ch}&fp=${fp}`;
    //                 if (typeof callback === "function") {
    //                     callback(null, finalUrl);
    //                 }
    //             })
    //             .catch(err => {
    //                 if (typeof callback === "function") callback(err, null);
    //             });

    //     } catch (err) {
    //         if (typeof callback === "function") callback(err, null);
    //     }
    // }

    // get_direct_watch_link: async function (rid, ch, callback) {


    //     // const secretKey = "[pW4X+6B4(pf&-dQ";
    //     const secretKey = "S8fXftT";
    //     const encoder = new TextEncoder();

    //     try {
    //         const keyData = encoder.encode(secretKey);
    //         const key = await crypto.subtle.importKey(
    //             "raw", keyData, { name: "HMAC", hash: "SHA-256" }, false, ["sign"]
    //         );

    //         const dataToSign = encoder.encode(rid + ch);
    //         const signature = await crypto.subtle.sign("HMAC", key, dataToSign);

    //         const hashArray = Array.from(new Uint8Array(signature));
    //         const b64Token = btoa(String.fromCharCode.apply(null, hashArray));

    //         const finalUrl = `https://rm.freex2line.online/2020/02/blog-post.html/get-link.php?request_id=${rid}&hmac_token=${encodeURIComponent(b64Token)}&ch=${ch}`;

    //         // إرجاع الرابط عبر الـ callback
    //         if (typeof callback === "function") {
    //             callback(null, finalUrl);
    //         }

    //     } catch (err) {
    //         console.error("[✘] خطأ في عملية التشفير: " + err.message);

    //         // تمرير الخطأ للـ callback إذا حدثت مشكلة
    //         if (typeof callback === "function") {
    //             callback(err, null);
    //         }
    //     }

    // }
};

mou_aflam_servers_array["CimaNow"] = obj;