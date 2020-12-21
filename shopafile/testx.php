<?php
echo (5*5);
/*
public function CreateContact($contact)
{
    $contactObj = self::factory();
    // check if contact data is valid
    $IsValid = $contactObj->validate($contact);
    // if valid, start to process
    if ($IsValid)
    {
        // make sure phone # isn't empty
        if ($contact['PhoneNum'] != '')
        {
            $contact['PhoneNum'] = self::clean_number($contact['PhoneNum']);
            $contact['can_contact'] = 1;
            EmailQueue::add_email($contact['email'], 'New');
        }
        else
        {
            $contact['can_contact'] = 0;
            EmailQueue::add_email($contact['email'], 'New');
        }
        // populate the contact in the main contact DB
        $contactObj->populate($contact);
        $success = $contactObj->insertDB();
        return true;
    }
    return false;
}
*/
?>
