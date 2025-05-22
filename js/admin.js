jQuery(function($) {
    var acc = document.getElementsByClassName("accordion");
    var i;

    for (i = 0; i < acc.length; i++) {
      acc[i].addEventListener("click", function() {
        this.classList.toggle("active");
        var panel = this.nextElementSibling;
        if (panel.style.maxHeight){
          panel.style.maxHeight = null;
        } else {
          panel.style.maxHeight = panel.scrollHeight + "px";
        } 
      });
    }

    $('.preload_template').change(function(event){
        var selected_template = $(this).val()
        var data_level = $(this).data('level')

        var url, payload, method;

        $('.handl-woo-ga-desc').addClass('handl-hide')
        if (selected_template == 'custom'){
            url = 'https://example.com/webhook/ipn.php'
            payload = 'gclid=wc|meta__gclid&amount=wc|data__total&cur=wc|data__currency&utm_source=handl|utm_source&status=payment_complete'
            method = 'POST'
        }else if (selected_template == 'ga'){
            url = 'https://www.google-analytics.com/collect'
            payload = 'v=1&t=event&tid=UA-XXXXX-X&cid=wc|data__customer_id&ti=wc|data__order_key&tr=wc|data__total&tt=wc|data__total_tax&ts=wc|data__shipping_total&tcc=COUPON&pa=purchase&pr1id=wc|product__id&pr1nm=wc|product__name&pr1qt=1&pr1pr=wc|data__total&ni=1&cu=USD&cn=wc|meta__utm_campaign&cs=wc|meta__utm_source&cm=wc|meta__utm_medium&ck=wc|meta__utm_keyword&cc=wc|meta__utm_content'
            method = 'POST'
            $('.handl-woo-ga-desc').removeClass('handl-hide')
        }else if (selected_template == 'ga4'){
            url = 'https://www.google-analytics.com/mp/collect?api_secret={secret_key}&measurement_id=G-XXXXXXX'
            payload = 'client_id=wc|meta__gaclientid&user_id=wc|data__customer_id&non_personalized_ads=false&events[0][name]=purchase&events[0][params][items][0][item_id]=wc|product__id&events[0][params][items][0][item_name]=wc|product__name&events[0][params][items][0][quantity]=wc|item__quantity&events[0][params][items][0][tax]=wc|data__total_tax&events[0][params][items][0][price]=wc|data__total&events[0][params][items][0][currency]=wc|data__currency&events[0][params][currency]=wc|data__currency&events[0][params][transaction_id]=wc|data__order_key&events[0][params][shipping]=wc|data__shipping_total&events[0][params][tax]=wc|data__total_tax&events[0][params][value]=wc|data__total&events[0][params][affiliation]=HANDL_WP&events[0][params][items][0][affiliation]=HANDL_WP&events[0][params][items][0][item_brand]=HANDL_WP&events[0][params][items][0][item_category]=HANDL_WP_CAT&events[0][params][items][0][item_variant]=HANDL_WP_UTM_GRABBER&events[0][params][items][0][coupon]=HANDL_COUPON&events[0][params][items][0][discount]=0&events[0][params][coupon]=HANDL_COUPON'
            method = 'POST'
            $('.handl-woo-ga-desc').removeClass('handl-hide')
        }else if (selected_template == 'fb'){
            url = 'Not needed for FB'
            payload = 'user[email]=wc|data__billing__email&user[city]=wc|data__billing__city&user[country_code]=wc|data__billing__country&user[first_name]=wc|data__billing__first_name&user[last_name]=wc|data__billing__last_name&user[state]=wc|data__billing__state&user[zip_code]=wc|data__billing__postcode&user[phone]=wc|data__billing__phone&user[fbp]=wc|meta___fbp&user[fbc]=wc|meta___fbc&user[client_ip_address]=wc|data__customer_ip_address&user[client_user_agent]=wc|data__customer_user_agent&custom[currency]=wc|data__currency&custom[value]=wc|data__total&custom[order_id]=wc|data__order_key&event[event_name]=Purchase&event[event_time]=now&event[event_id]=wc|data__order_key'
            method = 'POST'
        }

        $('#method_'+data_level).val(method)
        $('#woo_postback_url_'+data_level).val(url)
        $('#woo_postback_payment_complete_payload_'+data_level).val(payload)

    })

    $('#handl-report-form-plugins').change(function(event){
        var selected_form_plugin = $(this).val()
        $("#handl-report-error").addClass('handl-hide')
        $("#handl-report-forms").parent().addClass('handl-hide-load is-loading')
        $("#handl-report-date").addClass('handl-hide')
        $("#handl-report-submit").addClass('handl-hide')
        jQuery.post(
            ajaxurl,
            {
                'action': 'handl_report_list_forms',
                'selected_form_plugin': selected_form_plugin
            },
            function(response){
                if (response.error) {
                    $("#handl-report-error").removeClass('handl-hide')
                    $("#handl-report-error").html("<p>"+response.error+"</p>")
                }else{
                    // console.log(response)
                    if (response.forms){
                        $("#handl-report-forms").parent().removeClass('handl-hide-load is-loading')
                        $("#handl-report-date").removeClass('handl-hide')
                        $("#handl-report-submit").removeClass('handl-hide')
                        var dropdown = $("#handl-report-forms").empty()
                        let need_triggering_report_submit = false
                        $.each(response.forms, function (key, entry) {
                            let is_selected = HandLAdminReportInsight && HandLAdminReportInsight['selected_form_ids'] && HandLAdminReportInsight['selected_form_ids'].includes(entry.value.toString());
                            dropdown.append($('<option></option>').attr('value', entry.value).attr('selected', is_selected).text(entry.name))
                            if (is_selected){
                                need_triggering_report_submit = true
                            }
                        })
                        if (need_triggering_report_submit){
                            $('#handl-report-submit').trigger("click")
                            // console.log("Generating the report...")
                        }
                    }
                }
            }
        );
    })

    $('#handl-report-submit').click(function(event){

        let parent_div = $(this).parent()
        parent_div.addClass('handl-hide-load is-loading')
        $("#handl-report-error").addClass('handl-hide')
        $(".handl-report-container").addClass('handl-hide')

        jQuery.post(
            ajaxurl,
            {
                'action': 'handl_report_get_entries',
                'selected_form_plugin': $('#handl-report-form-plugins').val(),
                'selected_form_ids': $('#handl-report-forms').val(),
                'selected_start_date': $('#handl-report-start-date').val(),
                'selected_end_date': $('#handl-report-end-date').val()
            },
            function(response){
                // console.log(response)
                // response.entries = response.entries.slice(0,75)
                // console.log(response.entries)
                parent_div.removeClass('handl-hide-load is-loading')
                // console.log(response.error)
                if (response.error) {
                    $("#handl-report-error").removeClass('handl-hide')
                    $("#handl-report-error").html("<p>"+response.error+"</p>")
                }else{
                    $(".handl-report-container").removeClass('handl-hide')
                    // console.log(response)
                    let params = ['utm_campaign','utm_source','utm_medium','utm_content','utm_term','traffic_source']
                    for (param of params){

                        let data_obj = response.entries.map(a => a[param])
                        // console.log(data_obj)

                        let data_obj_freq = {};
                        for (const num of data_obj) {
                            data_obj_freq[num] = data_obj_freq[num] ? data_obj_freq[num] + 1 : 1;
                        }
                        // console.log(data_obj_freq)

                        // let data_obj_freq_top_10 = Object.fromEntries(Object.entries(data_obj_freq).slice(0,5))
                        // console.log(data_obj_freq_top_10)

                        /** Create Table **/
                        $('#handl_report_'+param+'_table').empty()
                        var tbl = $('<table></table>').attr({ class: "handl-report-table" });
                        var row = $('<tr></tr>').appendTo(tbl);
                        $('<th></th>').text(param.toUpperCase()).appendTo(row);
                        $('<th></th>').text("Occurance").appendTo(row);
                        for (const [key, value] of Object.entries(data_obj_freq)) {
                            var row = $('<tr></tr>').appendTo(tbl);
                            $('<td></td>').text(key).appendTo(row);
                            $('<td></td>').text(value).appendTo(row);
                        }

                        tbl.appendTo($('#handl_report_'+param+'_table'));

                        /** Create Chart **/
                        let border_colors = ["rgba(85, 37, 130, 1)", "rgba(230, 25, 75, 1)", "rgba(60, 180, 75, 1)", "rgba(255, 225, 25, 1)", "rgba(0, 130, 200, 1)", "rgba(245, 130, 48, 1)", "rgba(145, 30, 180, 1)", "rgba(70, 240, 240, 1)", "rgba(240, 50, 230, 1)", "rgba(210, 245, 60, 1)", "rgba(250, 190, 212, 1)", "rgba(0, 128, 128, 1)", "rgba(220, 190, 255, 1)", "rgba(170, 110, 40, 1)", "rgba(255, 250, 200, 1)"]
                        let background_colors = border_colors.map(function(x){return x.replace(", 1)", ", 0.5)")})
                        let ctx = document.getElementById('handl_report_'+param+'_chart');
                        if (Chart.getChart(ctx)) {
                            Chart.getChart(ctx).destroy();
                        }
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: Object.keys(data_obj_freq),
                                datasets: [{
                                    label: param,
                                    data: Object.values(data_obj_freq),
                                    borderWidth: 1,
                                    // backgroundColor: background_colors.slice(0, Object.keys(data_obj_freq).length),
                                    // borderColor: border_colors.slice(0, Object.keys(data_obj_freq).length),
                                }]
                            },
                            options: {
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: false,
                                    }
                                }
                            }
                        });
                    }

                }
            }
        );
    })


    $('#handl-report-generate-insight').click(function(event) {

        let parent_div = $(this).parent()
        parent_div.addClass('handl-hide-load is-loading')

        $("#no-license-key").addClass('handl-hide')
        $("#license-not-active").addClass('handl-hide')
        $("#not-enough-credits").addClass('handl-hide')



        var selected_form_plugin =  $('#handl-report-form-plugins').val()
        var selected_form_ids =  $('#handl-report-forms').val()
        var selected_start_date = $('#handl-report-start-date').val()
        var selected_end_date = $('#handl-report-end-date').val()

        var report_name = `${selected_form_plugin}-id-${selected_form_ids.join("-")}-date-${selected_start_date}-${selected_end_date}`
        // console.log(report_name)

        jQuery.post(
            ajaxurl,
            {
                'action': 'handl_report_generate_insight',
                'selected_form_plugin': $('#handl-report-form-plugins').val(),
                'selected_form_ids': $('#handl-report-forms').val(),
                'selected_start_date': $('#handl-report-start-date').val(),
                'selected_end_date': $('#handl-report-end-date').val()
            },
            function(response){
                parent_div.removeClass('handl-hide-load is-loading')
                if (response.success){
                    if (response.report_id){
                        window.location.href = window.location.pathname + "?page=handl_analytics&report_id=" + response.report_id
                    }
                }else if (response.error){
                    if (response.error == "No License Key Provided"){
                        $("#no-license-key").removeClass('handl-hide')
                    }else if (response.error == "Your license is not active"){
                        $("#license-not-active").removeClass('handl-hide')
                    }else if (response.error == "Not Enough Credit"){
                        $("#not-enough-credits").removeClass('handl-hide')
                    }
                }
            }
        );
    })

    $('#handl-report-delete-report').click(function(event) {

        let parent_div = $(this).parent()
        parent_div.addClass('handl-hide-load is-loading')

        jQuery.post(
            ajaxurl,
            {
                'action': 'handl_report_delete_report',
                'report_name': HandLAdminReportInsight["report_name"],
            },
            function (response) {
                // console.log(response)
                if (response.success){
                    window.location.href = window.location.pathname + "?page=handl_analytics"
                }
            })
    })

    if ( HandLAdminReportInsight && HandLAdminReportInsight['selected_form_plugin'] ){
        $('#handl-report-form-plugins').trigger('change');
        // console.log("Selecting the form...")
    }

    // $('.handl-fb-login').click(function(){
    //     jQuery.post(
    //         ajaxurl,
    //         {
    //             'action': 'handl_fb_login',
    //             'current_url': window.location.href.replace(/#$/,"")
    //         },
    //         function(response) {
    //             if (response.error){
    //                 alert(response.error)
    //             }else if (response.success && response.url){
    //                 // console.log(response.url)
    //                 // tb_show("HandL UTM Grabber - Facebook Offline Conversion Login", response.url);
    //                 location.href = response.url
    //             }
    //         }
    //     );
    // })
    //
    // $('#business_acc_0').change(function(event){
    //     $('.display_only_fb_pixel').show()
    //     $('.loading_fb_pix').show()
    //     var act_id = $(this).val()
    //     jQuery.post(
    //         ajaxurl,
    //         {
    //             'action': 'handl_fb_list_pixels',
    //             'act_id': act_id
    //         },
    //         function(response) {
    //             if (response.result.error){
    //                 alert(response.result.error.message)
    //             }else{
    //                 $('#pixel_id_0').empty()
    //                 $('#pixel_id_0').append(`<option value="">Select Pixel</option>`)
    //                 for (res of response.result){
    //                     $('#pixel_id_0').append(`<option value="${res.id}||${res.name}">${res.name} (${res.id}</option>`)
    //                 }
    //                 $('#pixel_id_0').trigger('change')
    //                 $('.loading_fb_pix').hide()
    //             }
    //
    //         }
    //     );
    // })
    //
    // $('#pixel_id_0').change(function(event){
    //     var pixel_id = $(this).val()
    //     if (pixel_id != ''){
    //         jQuery.post(
    //             ajaxurl,
    //             {
    //                 'action': 'handl_fb_save_pixel_id',
    //                 'pixel_id': pixel_id
    //             },
    //             function(response) {
    //
    //             }
    //         );
    //     }
    // })
    //
    // $('#unlink_act').click(function(event){
    //     jQuery.post(
    //         ajaxurl,
    //         {
    //             'action': 'handl_fb_unlink_act',
    //         },
    //         function(response) {
    //             $('#business_acc_0').prop('disabled', false)
    //             // $('#business_acc_0').trigger('change')
    //             $('#unlink_act').parent().hide()
    //         }
    //     );
    // })
    //
    // $('#unlink_pixel').click(function(event){
    //     jQuery.post(
    //         ajaxurl,
    //         {
    //             'action': 'handl_fb_unlink_pixel',
    //         },
    //         function(response) {
    //             $('#pixel_id_0').prop('disabled', false)
    //             $('#business_acc_0').trigger('change')
    //             $('#unlink_pixel').parent().hide()
    //         }
    //     );
    // })
    //
    // $('#unlink_fb').click(function(event){
    //     jQuery.post(
    //         ajaxurl,
    //         {
    //             'action': 'handl_fb_unlink_fb',
    //         },
    //         function(response) {
    //             $('.fb-login-link').show();
    //             $('.fb_is_authed').html("").hide();
    //             $('#unlink_fb').parent().hide()
    //             $('.display_only_fb_act').hide()
    //             $('.display_only_fb_pixel').hide()
    //         }
    //     );
    // })
    //
    // if ($('.preload_template').val() == 'fb'){
    //     load_act_block()
    // }

    jQuery(document).ready(function($) {
        $(".date-picker").datepicker({ dateFormat: 'yy-mm-dd' });

        var startDate = $("#handl-report-start-date-picker").val();
        var endDate = $("#handl-report-end-date-picker").val();

        $("#handl-report-start-date").val(startDate);
        $("#handl-report-end-date").val(endDate);

        $(".date-picker").on("change", function() {
            var startDate = $("#handl-report-start-date-picker").val();
            var endDate = $("#handl-report-end-date-picker").val();

            $("#handl-report-start-date").val(startDate);
            $("#handl-report-end-date").val(endDate);
        });
    });

});


// function load_act_block(){
//     $ = jQuery
//     $('.loading_fb_act').show()
//     jQuery.post(
//         ajaxurl,
//         {
//             'action': 'handl_fb_ready',
//         },
//         function(response) {
//             if (!response.is_authed){
//                 $('.display_only_fb_auth').show()
//             }else{
//                 $('.fb-login-link').hide();
//                 $('.fb_is_authed').html("You've been authorized").show();
//                 $('#unlink_fb').parent().show()
//
//                 $('.display_only_fb_act').show()
//                 if (!response.is_act_id_saved){
//                     jQuery.post(
//                         ajaxurl,
//                         {
//                             'action': 'handl_fb_list_acts',
//                         },
//                         function(response) {
//                             if (response.result.error){
//                                 alert(response.result.error.message)
//                             }else{
//                                 $('#business_acc_0').empty()
//                                 $('#business_acc_0').append(`<option value="">Select Business Account</option>`)
//                                 for (res of response.result){
//                                     var selected = res.selected ? 'selected' : ''
//                                     $('#business_acc_0').append(`<option value="${res.id}||${res.name}" ${selected}>${res.name} (${res.id})</option>`)
//                                 }
//                                 // $('#business_acc_0').trigger('change')
//                                 $('.loading_fb_act').hide()
//                             }
//                         }
//                     );
//
//                 }else{
//                     res = response.act
//                     $('#business_acc_0').prop('disabled', true)
//                     $('#business_acc_0').append(`<option value="${res.id}||${res.name}">${res.name} (${res.id})</option>`)
//                     $('.loading_fb_act').hide()
//                     $('#unlink_act').parent().show()
//
//                     if (response.is_pixel_saved){
//                         $('.display_only_fb_pixel').show()
//                         res = response.pixel
//                         $('#pixel_id_0').prop('disabled', true)
//                         $('#pixel_id_0').append(`<option value="${res.id}||${res.name}">${res.name} (${res.id})</option>`)
//                         $('.loading_fb_pix').hide()
//                         $('#unlink_pixel').parent().show()
//                     }else{
//                         $('#business_acc_0').trigger('change')
//                     }
//                 }
//             }
//
//
//
//
//         }
//     );
// }