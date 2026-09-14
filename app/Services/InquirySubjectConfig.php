<?php

namespace App\Services;

/**
 * Centralised subject → requirements/fields/validation mapping.
 *
 * Each subject entry defines:
 *   fields        – which extra fields appear on the form
 *   attachment    – whether the user may upload files
 *   attach_label  – label shown on the file-upload button
 *   attach_req    – true = at least one file is required
 *   requirements  – bullet-point list shown to the user as a hint
 *   validation    – extra Laravel validation rules merged into the base rules
 */
class InquirySubjectConfig
{
    /** @return array<string,array> */
    public static function all(): array
    {
        return [
            'General Inquiry' => [
                'fields'       => ['message'],
                'attachment'   => true,
                'attach_label' => 'Attachment (optional)',
                'attach_req'   => false,
                'requirements' => [],
                'validation'   => [],
            ],

            'Mass Schedule' => [
                'fields'       => ['message'],
                'attachment'   => true,
                'attach_label' => 'Attachment (optional)',
                'attach_req'   => false,
                'requirements' => [
                    'Specify the Mass type (regular, special, funeral, wedding, etc.)',
                    'Include the preferred date if applicable',
                ],
                'validation'   => [],
            ],

            'Sacrament Requirements' => [
                'fields'       => ['message'],
                'attachment'   => true,
                'attach_label' => 'Supporting Document(s)',
                'attach_req'   => false,
                'requirements' => [
                    'Baptism – Baptismal Certificate of the person',
                    'Confirmation – Baptismal Certificate & sponsor\'s confirmation certificate',
                    'Marriage – PSA Birth Certificate (both parties), CENOMAR, Pre-Cana seminar certificate',
                    'First Communion – Baptismal Certificate & attendance record',
                    'Death/Burial – Death Certificate or hospital record',
                ],
                'validation'   => [],
            ],

            'Booking / Appointment' => [
                'fields'       => ['preferred_date', 'preferred_time', 'message'],
                'attachment'   => true,
                'attach_label' => 'Attachment (optional)',
                'attach_req'   => false,
                'requirements' => [
                    'State the type of service you are booking',
                    'Preferred date and time are required',
                    'Attach any supporting documents if available',
                ],
                'validation'   => [
                    'preferred_date' => ['required', 'date', 'after_or_equal:today'],
                    'preferred_time' => ['required', 'string', 'max:10'],
                ],
            ],

            'Certificate Request' => [
                'fields'       => ['message'],
                'attachment'   => true,
                'attach_label' => 'Valid ID / Supporting Document(s)',
                'attach_req'   => true,
                'requirements' => [
                    'One (1) valid government-issued ID',
                    'Original baptismal / marriage / confirmation record if available',
                    'Authorization letter if requesting on behalf of another person',
                    'Parish processing fee may apply',
                ],
                'validation'   => [
                    'attachments'   => ['required'],
                    'attachments.*' => ['file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:5120'],
                ],
            ],

            'Donation / Support' => [
                'fields'       => ['message'],
                'attachment'   => true,
                'attach_label' => 'Donation Details / Proof (optional)',
                'attach_req'   => false,
                'requirements' => [
                    'Describe the type of donation or support you wish to offer',
                    'Include bank/GCash/Maya reference number if sending proof of donation',
                ],
                'validation'   => [],
            ],

            'Complaint / Feedback' => [
                'fields'       => ['message'],
                'attachment'   => true,
                'attach_label' => 'Evidence / Supporting File (optional)',
                'attach_req'   => false,
                'requirements' => [
                    'Be specific about the incident, date, and persons involved',
                    'Attach photos or documents as evidence if available',
                ],
                'validation'   => [],
            ],

            'Other' => [
                'fields'       => ['message'],
                'attachment'   => true,
                'attach_label' => 'Attachment (optional)',
                'attach_req'   => false,
                'requirements' => [],
                'validation'   => [],
            ],
        ];
    }

    /** Return config for a single subject, or the General Inquiry default. */
    public static function for(string $subject): array
    {
        return static::all()[$subject] ?? static::all()['General Inquiry'];
    }

    /** Base validation rules shared by every subject. */
    public static function baseRules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'subject'        => ['required', 'string', 'in:' . implode(',', array_keys(static::all()))],
            'message'        => ['required', 'string', 'max:5000'],
            'preferred_date' => ['nullable', 'date', 'after_or_equal:today'],
            'preferred_time' => ['nullable', 'string', 'max:10'],
            'attachments'    => ['nullable', 'array', 'max:5'],
            'attachments.*'  => ['file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:5120'],
        ];
    }

    /** Merge base rules with the subject-specific overrides. */
    public static function rulesFor(string $subject): array
    {
        $extra = static::for($subject)['validation'] ?? [];
        return array_merge(static::baseRules(), $extra);
    }

    /** JSON-safe summary for the frontend JS. */
    public static function forJs(): string
    {
        $out = [];
        foreach (static::all() as $subject => $cfg) {
            $out[$subject] = [
                'fields'      => $cfg['fields'],
                'attachment'  => $cfg['attachment'],
                'attachLabel' => $cfg['attach_label'],
                'attachReq'   => $cfg['attach_req'],
                'requirements'=> $cfg['requirements'],
            ];
        }
        return json_encode($out, JSON_UNESCAPED_UNICODE);
    }
}
