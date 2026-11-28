<?php
return ['staff_mfa'=>(bool)env('VERDLEAF_STAFF_MFA',false),'preview' => (bool) env('VERDLEAF_PREVIEW', true), 'sales_email'=>env('VERDLEAF_SALES_EMAIL'),'preview_key'=>env('VERDLEAF_PREVIEW_KEY'),'preview_user'=>env('VERDLEAF_PREVIEW_USER','staging'),'google_verification'=>env('VERDLEAF_GOOGLE_VERIFICATION')];
