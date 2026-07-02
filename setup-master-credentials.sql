USE seo_system;

-- Delete old entries for user_id=1
DELETE FROM social_accounts WHERE user_id = 1;

-- Insert all best credentials for user_id = 1 (master account)
INSERT INTO social_accounts (user_id, platform, username, password, api_key, api_secret) VALUES
(1, 'bluesky',    'learnmore08.bsky.social',      TO_BASE64('3g34-w47k-z3p2-2ixz'), '', ''),
(1, 'devto',      'kanzariyapratik007',            '', 'Dvk8kAnh24fCeSwith8CNXN3', ''),
(1, 'github',     'Leranmore123',                  '', 'ghp_aOY0S3tJp2sGumchejMbRfglWMnH1jRa3RaX', ''),
(1, 'hashnode',   'kanzariyapratik007',            '', '89bdd968-90fd-43ef-a9ab-9236f19f5e8a', ''),
(1, 'pinterest',  'Learnmore08',                   '', 'pina_AMA3VXIXAASKOBQAGDAOID6455MIIAAAAAAAAAAAAAAAAAAAAAAAAACQ4GCAAA', ''),
(1, 'tumblr',     'learnmoretech',                 '', 'qQbjzZYmeW8h7sQ1MfYFdhhOMQ2Lni33l9hKMuBp7iGfpP2kT5', 'b74e9RU2091be6ZWgFTzGhiGmXMBmz1kiRejh3c9pYKxRMHH70'),
(1, 'wordpress',  'learnmoretech.in',              '', '141079', 'V4vTd95NVEIBHNYfFXLBUgCRSa70CrcWiPaLdKRuOOCV3VltBz3nvsG2wVLFVOAV'),
(1, 'blogger',    'kanzariyapratik124@gmail.com',  '', 'ya29.a0AQvPyIMgaQtpF8DprtViQFLwlpQ', '2201670847900613032'),
(1, 'minds',      'kanzariyapratik124@gmail.com',  TO_BASE64('@Disha12@'), 'mdb_sMBf4xqJ.3hh31XeJSuHdTh0uNFH', ''),
(1, 'mediafire',  'kanzariyapratik124@gmail.com',  TO_BASE64('@DISHA12@'), '', ''),
(1, 'fourshared', 'kanzariyapratik124@gmail.com',  TO_BASE64('@DISHA12@'), '', ''),
(1, 'gifyu',      'kanzariyapratik124@gmail.com',  TO_BASE64('@Disha12@'), '', ''),
(1, 'pdfhost',    'kanzariyapratik124@gmail.com',  TO_BASE64('@DISHA12@'), '', ''),
(1, 'medium',     'kanzariyapratik124@gmail.com',  TO_BASE64('@DISHA12@'), '', '');

SELECT platform, username, LEFT(api_key,25) as key_preview FROM social_accounts WHERE user_id=1 ORDER BY platform;
