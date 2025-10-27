// নিশ্চিত করা যে DOM সম্পূর্ণ লোড হওয়ার পরেই jQuery কোড রান করবে
$(document).ready(function() {

    // ===============================================
    // "সেভ করুন" বাটনে ক্লিক করলে কী হবে
    // ===============================================
    $('#saveMedicineBtn').on('click', function(e) {
        e.preventDefault(); 
        var formData = $('#addMedicineForm').serialize();
        formData = formData + '&action=add_medicine';

        var medName = $('#med_name').val();
        var medStock = $('#med_stock').val();
        var medMrp = $('#med_mrp').val();

        if (medName === '' || medStock === '' || medMrp === '') {
            alert('অনুগ্রহ করে তারকা (*) চিহ্নিত সব ফিল্ড পূরণ করুন।');
            return false; 
        }

        $.ajax({
            type: 'POST',
            url: 'ajax/medicine_actions.php',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert(response.message);
                    $('#addMedicineModal').modal('hide');
                    $('#addMedicineForm')[0].reset();
                    location.reload();
                } else {
                    alert('Error: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + " - " + error);
                alert('সার্ভারের সাথে সংযোগে সমস্যা হয়েছে।');
            }
        });
    });

    // ===============================================
    // ইনভেন্টরি পেজে ডিলিট বাটন হ্যান্ডেল করা
    // ===============================================
    $('#medicineTableBody').on('click', '.deleteBtn', function() {
        var $thisButton = $(this); 
        var medicineId = $thisButton.data('id'); 

        if (confirm('আপনি কি এই ওষুধটি স্থায়ীভাবে মুছে ফেলতে নিশ্চিত?')) {
            $.ajax({
                type: 'POST',
                url: 'ajax/medicine_actions.php',
                data: {
                    action: 'delete_medicine',
                    id: medicineId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        $thisButton.closest('tr').fadeOut(500, function() {
                            $(this).remove();
                        });
                    } else {
                        alert('Error: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function() {
                    alert('সার্ভারের সাথে সংযোগে সমস্যা হয়েছে।');
                }
            });
        }
    });

    // ===============================================
    // এডিট বাটনে ক্লিক করলে ডেটা লোড করা
    // ===============================================
    $('#medicineTableBody').on('click', '.editBtn', function() {
        var medicineId = $(this).data('id');

        $.ajax({
            type: 'POST',
            url: 'ajax/medicine_actions.php',
            data: {
                action: 'get_medicine_details',
                id: medicineId
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    var med = response.data;
                    $('#edit_med_id').val(med.id);
                    $('#edit_med_name').val(med.name);
                    $('#edit_med_batch').val(med.batch_no);
                    $('#edit_med_expiry').val(med.expiry_date);
                    $('#edit_med_stock').val(med.stock_quantity);
                    $('#edit_med_mrp').val(med.mrp);
                    $('#edit_med_cost').val(med.cost_price);
                    $('#editMedicineModal').modal('show');
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('সার্ভারের সাথে সংযোগে সমস্যা হয়েছে। (ডেটা আনতে ব্যর্থ)');
            }
        });
    });

    // ===============================================
    // "আপডেট করুন" বাটনে ক্লিক করলে ডেটা সেভ করা
    // ===============================================
    $('#updateMedicineBtn').on('click', function(e) {
        e.preventDefault();

        var formData = $('#editMedicineForm').serialize();
        formData = formData + '&action=update_medicine';

        $.ajax({
            type: 'POST',
            url: 'ajax/medicine_actions.php',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert(response.message);
                    $('#editMedicineModal').modal('hide');
                    location.reload(); 
                } else {
                    alert('Error: ' + (response.message || 'Unknown error'));
                }
            },
            error: function() {
                alert('সার্ভারের সাথে সংযোগে সমস্যা হয়েছে। (আপডেট করতে ব্যর্থ)');
            }
        });
    });

    // ===============================================
    // বিলিং পেজের লজিক
    // ===============================================

    if ($('#medicine_search').length > 0) {
        
        var billingCart = []; // কার্ট হিসাব রাখার জন্য

        // ১. jQuery UI Autocomplete (লাইভ সার্চ)
        $("#medicine_search").autocomplete({
            source: function(request, response) {
                $.ajax({
                    type: 'POST',
                    url: 'ajax/billing_actions.php',
                    dataType: "json",
                    data: {
                        term: request.term,
                        action: 'search_medicine'
                    },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            minLength: 1,
            select: function(event, ui) { 
                event.preventDefault();
                var med_id = ui.item.id;
                var med_name = ui.item.label;
                var med_mrp = ui.item.mrp;
                var med_stock = ui.item.stock;
                addToCart(med_id, med_name, med_mrp, med_stock);
                $(this).val(''); 
            }
        });

        // ২. কার্টে ওষুধ যোগ করার ফাংশন
        function addToCart(id, name, mrp, stock) {
            var existingItem = $(`#billing-cart-body tr[data-id='${id}']`);
            if (existingItem.length > 0) {
                var qtyInput = existingItem.find('.cart-quantity');
                var newQty = parseInt(qtyInput.val()) + 1;
                if (newQty > stock) {
                    alert('স্টক শেষ! আর যোগ করা সম্ভব নয়। (সর্বোচ্চ: ' + stock + ')');
                    return;
                }
                qtyInput.val(newQty);
            } else {
                if ($('#billing-cart-body tr td[colspan="5"]').length > 0) {
                    $('#billing-cart-body').empty();
                }

                var newRow = `
                    <tr data-id="${id}" data-stock="${stock}" data-mrp="${mrp}">
                        <td>${name}</td>
                        <td>${mrp}</td>
                        <td><input type="number" class="form-control cart-quantity" value="1" min="1" max="${stock}"></td>
                        <td class="row-total">${mrp}</td>
                        <td><button class="btn btn-danger btn-sm delete-cart-item"><i class="bi bi-trash-fill"></i></button></td>
                    </tr>
                `;
                $('#billing-cart-body').append(newRow);
            }
            updateBillTotal();
        }

        // ৩. মোট বিল গণনা
        function updateBillTotal() {
            var subtotal = 0;
            $('#billing-cart-body tr').each(function() {
                if ($(this).data('id')) {
                    var mrp = parseFloat($(this).data('mrp'));
                    var quantity = parseInt($(this).find('.cart-quantity').val());
                    var rowTotal = mrp * quantity;
                    $(this).find('.row-total').text(rowTotal.toFixed(2)); 
                    subtotal += rowTotal;
                }
            });
            var discount = parseFloat($('#bill-discount').val()) || 0;
            var grandTotal = subtotal - discount;

            $('#bill-subtotal').text('৳ ' + subtotal.toFixed(2));
            $('#bill-grandtotal').text('৳ ' + grandTotal.toFixed(2));
        }

        // ৪. quantity বা discount পরিবর্তনে আপডেট
        $('#billing-cart-body').on('change keyup', '.cart-quantity', function() {
            var $row = $(this).closest('tr');
            var stock = parseInt($row.data('stock'));
            var currentQty = parseInt($(this).val());
            if (currentQty > stock) {
                alert('স্টক শেষ! সর্বোচ্চ পরিমাণ: ' + stock);
                $(this).val(stock);
            }
            if (currentQty < 1) {
                $(this).val(1);
            }
            updateBillTotal();
        });

        $('#bill-discount').on('change keyup', function() {
            updateBillTotal();
        });

        // ৫. কার্ট থেকে আইটেম ডিলিট
        $('#billing-cart-body').on('click', '.delete-cart-item', function() {
            if (confirm('আপনি কি এই আইটেমটি কার্ট থেকে মুছতে চান?')) {
                $(this).closest('tr').remove();
                if ($('#billing-cart-body tr').length === 0) {
                     $('#billing-cart-body').html('<tr><td colspan="5" class="text-center text-muted">কার্ট খালি আছে</td></tr>');
                }
                updateBillTotal();
            }
        });

        // ৬. সম্পূর্ণ বিল বাতিল করা
        $('#cancelBillBtn').on('click', function() {
             if (confirm('আপনি কি এই সম্পূর্ণ বিলটি বাতিল করতে চান?')) {
                 $('#billing-cart-body').html('<tr><td colspan="5" class="text-center text-muted">কার্ট খালি আছে</td></tr>');
                 $('#bill-discount').val('0.00');
                 $('#customer_name').val('Walking Customer');
                 updateBillTotal();
             }
        });

        // ===============================================
        // ৭. "বিল জেনারেট করুন" বাটনে ক্লিক
        // ===============================================
        $('#generateBillBtn').on('click', function(e) {
            e.preventDefault();

            // ১. কার্ট আইটেম সংগ্রহ
            var cartItems = [];
            $('#billing-cart-body tr').each(function() {
                var row = $(this);
                if (row.data('id')) {
                    cartItems.push({
                        id: row.data('id'),
                        name: row.find('td:first').text(),
                        mrp: row.data('mrp'),
                        quantity: parseInt(row.find('.cart-quantity').val())
                    });
                }
            });

            if (cartItems.length === 0) {
                alert('বিল তৈরি করতে অনুগ্রহ করে অন্তত একটি ওষুধ কার্টে যোগ করুন।');
                return;
            }

            var customerName = $('#customer_name').val();
            var subTotal = parseFloat($('#bill-subtotal').text().replace('৳ ', ''));
            var discount = parseFloat($('#bill-discount').val());
            var grandTotal = parseFloat($('#bill-grandtotal').text().replace('৳ ', ''));

            if (!confirm('আপনি কি বিলটি জেনারেট করতে নিশ্চিত? মোট: ' + grandTotal.toFixed(2) + ' ৳')) {
                return;
            }

            var postData = {
                action: 'generate_bill',
                customer_name: customerName,
                sub_total: subTotal,
                discount: discount,
                grand_total: grandTotal,
                cart_items: JSON.stringify(cartItems)
            };

            $.ajax({
                type: 'POST',
                url: 'ajax/billing_actions.php',
                data: postData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        window.location.href = 'receipt.php?sale_id=' + response.sale_id;
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('সার্ভারের সাথে সংযোগে সমস্যা হয়েছে। বিল জেনারেট করা যায়নি।');
                }
            });
        });
    }
});
