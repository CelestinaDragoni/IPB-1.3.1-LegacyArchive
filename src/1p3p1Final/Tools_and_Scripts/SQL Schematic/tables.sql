CREATE TABLE ibf_admin_logs (
  id bigint(20) NOT NULL auto_increment,
  act varchar(255) default NULL,
  code varchar(255) default NULL,
  member_id int(10) default NULL,
  ctime int(10) default NULL,
  note text,
  ip_address varchar(255) default NULL,
  PRIMARY KEY  (id)
);

CREATE TABLE ibf_admin_sessions (
  ID varchar(32) NOT NULL default '',
  IP_ADDRESS varchar(32) NOT NULL default '',
  MEMBER_NAME varchar(32) NOT NULL default '',
  MEMBER_ID varchar(32) NOT NULL default '',
  SESSION_KEY varchar(32) NOT NULL default '',
  LOCATION varchar(64) default 'index',
  LOG_IN_TIME int(10) NOT NULL default '0',
  RUNNING_TIME int(10) NOT NULL default '0',
  PRIMARY KEY  (ID)
);

CREATE TABLE ibf_badwords (
  wid int(3) NOT NULL auto_increment,
  type varchar(250) NOT NULL default '',
  swop varchar(250) default NULL,
  m_exact tinyint(1) default '0',
  PRIMARY KEY  (wid)
);

CREATE TABLE ibf_cache_store (
  cs_key varchar(255) NOT NULL default '',
  cs_value text NOT NULL,
  cs_extra varchar(255) NOT NULL default '',
  PRIMARY KEY  (cs_key)
);

CREATE TABLE ibf_calendar_events (
  eventid mediumint(8) NOT NULL auto_increment,
  userid mediumint(8) NOT NULL default '0',
  year int(4) NOT NULL default '2002',
  month int(2) NOT NULL default '1',
  mday int(2) NOT NULL default '1',
  title varchar(254) NOT NULL default 'no title',
  event_text text NOT NULL,
  read_perms varchar(254) NOT NULL default '*',
  unix_stamp int(10) NOT NULL default '0',
  priv_event tinyint(1) NOT NULL default '0',
  show_emoticons tinyint(1) NOT NULL default '1',
  rating smallint(2) NOT NULL default '1',
  event_ranged tinyint(1) NOT NULL default '0',
  event_repeat tinyint(1) NOT NULL default '0',
  repeat_unit char(2) NOT NULL default '',
  end_day int(2) default NULL,
  end_month int(2) default NULL,
  end_year int(4) default NULL,
  end_unix_stamp int(10) default NULL,
  event_bgcolor varchar(32) NOT NULL default '',
  event_color varchar(32) NOT NULL default '',
  PRIMARY KEY  (eventid),
  KEY unix_stamp (unix_stamp)
);

CREATE TABLE ibf_categories (
  id smallint(5) NOT NULL default '0',
  position tinyint(3) default NULL,
  state varchar(10) default NULL,
  name varchar(128) NOT NULL default '',
  description text,
  image varchar(128) default NULL,
  url varchar(128) default NULL,
  PRIMARY KEY  (id),
  KEY id (id)
);

CREATE TABLE ibf_contacts (
  id mediumint(8) NOT NULL auto_increment,
  contact_id mediumint(8) NOT NULL default '0',
  member_id mediumint(8) NOT NULL default '0',
  contact_name varchar(32) NOT NULL default '',
  allow_msg tinyint(1) default NULL,
  contact_desc varchar(50) default NULL,
  PRIMARY KEY  (id)
);

CREATE TABLE ibf_css (
  cssid int(10) NOT NULL auto_increment,
  css_name varchar(128) NOT NULL default '',
  css_text text,
  css_comments text,
  updated int(10) default '0',
  PRIMARY KEY  (cssid)
);

CREATE TABLE ibf_email_logs (
  email_id int(10) NOT NULL auto_increment,
  email_subject varchar(255) NOT NULL default '',
  email_content text NOT NULL,
  email_date int(10) NOT NULL default '0',
  from_member_id mediumint(8) NOT NULL default '0',
  from_email_address varchar(250) NOT NULL default '',
  from_ip_address varchar(16) NOT NULL default '127.0.0.1',
  to_member_id mediumint(8) NOT NULL default '0',
  to_email_address varchar(250) NOT NULL default '',
  topic_id int(10) NOT NULL default '0',
  PRIMARY KEY  (email_id),
  KEY from_member_id (from_member_id),
  KEY email_date (email_date)
);

CREATE TABLE ibf_emoticons (
  id smallint(3) NOT NULL auto_increment,
  typed varchar(32) NOT NULL default '',
  image varchar(128) NOT NULL default '',
  clickable smallint(2) NOT NULL default '1',
  PRIMARY KEY  (id)
);

CREATE TABLE ibf_faq (
  id mediumint(8) NOT NULL auto_increment,
  title varchar(128) NOT NULL default '',
  text text,
  description text NOT NULL,
  PRIMARY KEY  (id)
);

CREATE TABLE ibf_forum_perms (
  perm_id int(10) NOT NULL auto_increment,
  perm_name varchar(250) NOT NULL default '',
  PRIMARY KEY  (perm_id)
);

CREATE TABLE ibf_forum_tracker (
  frid mediumint(8) NOT NULL auto_increment,
  member_id varchar(32) NOT NULL default '',
  forum_id smallint(5) NOT NULL default '0',
  start_date int(10) default NULL,
  last_sent int(10) NOT NULL default '0',
  PRIMARY KEY  (frid)
);

CREATE TABLE ibf_forums (
  id smallint(5) NOT NULL default '0',
  topics mediumint(6) default NULL,
  posts mediumint(6) default NULL,
  last_post int(10) default NULL,
  last_poster_id mediumint(8) NOT NULL default '0',
  last_poster_name varchar(32) default NULL,
  name varchar(128) NOT NULL default '',
  description text,
  position tinyint(2) default NULL,
  use_ibc tinyint(1) default NULL,
  use_html tinyint(1) default NULL,
  status varchar(10) default NULL,
  start_perms varchar(255) NOT NULL default '',
  reply_perms varchar(255) NOT NULL default '',
  read_perms varchar(255) NOT NULL default '',
  password varchar(32) default NULL,
  category tinyint(2) NOT NULL default '0',
  last_title varchar(128) default NULL,
  last_id int(10) default NULL,
  sort_key varchar(32) default NULL,
  sort_order varchar(32) default NULL,
  prune tinyint(3) default NULL,
  show_rules tinyint(1) default NULL,
  upload_perms varchar(255) default NULL,
  preview_posts tinyint(1) default NULL,
  allow_poll tinyint(1) NOT NULL default '1',
  allow_pollbump tinyint(1) NOT NULL default '0',
  inc_postcount tinyint(1) NOT NULL default '1',
  skin_id int(10) default NULL,
  parent_id mediumint(5) default '-1',
  subwrap tinyint(1) default '0',
  sub_can_post tinyint(1) default '1',
  quick_reply tinyint(1) default '0',
  redirect_url varchar(250) default '',
  redirect_on tinyint(1) NOT NULL default '0',
  redirect_hits int(10) NOT NULL default '0',
  redirect_loc varchar(250) default '',
  rules_title varchar(255) NOT NULL default '',
  rules_text text NOT NULL,
  has_mod_posts tinyint(1) NOT NULL default '0',
  topic_mm_id varchar(250) NOT NULL default '',
  notify_modq_emails text default '',
  PRIMARY KEY  (id),
  KEY category (category),
  KEY id (id)
);

CREATE TABLE ibf_groups (
  g_id int(3) unsigned NOT NULL auto_increment,
  g_view_board tinyint(1) default NULL,
  g_mem_info tinyint(1) default NULL,
  g_other_topics tinyint(1) default NULL,
  g_use_search tinyint(1) default NULL,
  g_email_friend tinyint(1) default NULL,
  g_invite_friend tinyint(1) default NULL,
  g_edit_profile tinyint(1) default NULL,
  g_post_new_topics tinyint(1) default NULL,
  g_reply_own_topics tinyint(1) default NULL,
  g_reply_other_topics tinyint(1) default NULL,
  g_edit_posts tinyint(1) default NULL,
  g_delete_own_posts tinyint(1) default NULL,
  g_open_close_posts tinyint(1) default NULL,
  g_delete_own_topics tinyint(1) default NULL,
  g_post_polls tinyint(1) default NULL,
  g_vote_polls tinyint(1) default NULL,
  g_use_pm tinyint(1) default NULL,
  g_is_supmod tinyint(1) default NULL,
  g_access_cp tinyint(1) default NULL,
  g_title varchar(32) NOT NULL default '',
  g_can_remove tinyint(1) default NULL,
  g_append_edit tinyint(1) default NULL,
  g_access_offline tinyint(1) default NULL,
  g_avoid_q tinyint(1) default NULL,
  g_avoid_flood tinyint(1) default NULL,
  g_icon varchar(64) default NULL,
  g_attach_max bigint(20) default NULL,
  g_avatar_upload tinyint(1) default '0',
  g_calendar_post tinyint(1) default '0',
  prefix varchar(250) default NULL,
  suffix varchar(250) default NULL,
  g_max_messages int(5) default '50',
  g_max_mass_pm int(5) default '0',
  g_search_flood mediumint(6) default '20',
  g_edit_cutoff int(10) default '0',
  g_promotion varchar(10) default '-1&-1',
  g_hide_from_list tinyint(1) default '0',
  g_post_closed tinyint(1) default '0',
  g_perm_id varchar(255) NOT NULL default '',
  g_photo_max_vars varchar(200) default '',
  g_dohtml tinyint(1) NOT NULL default '0',
  g_edit_topic tinyint(1) NOT NULL default '0',
  g_email_limit varchar(15) NOT NULL default '10:15',
  PRIMARY KEY  (g_id)
);

CREATE TABLE ibf_languages (
  lid mediumint(8) NOT NULL auto_increment,
  ldir varchar(64) NOT NULL default '',
  lname varchar(250) NOT NULL default '',
  lauthor varchar(250) default NULL,
  lemail varchar(250) default NULL,
  PRIMARY KEY  (lid)
);

CREATE TABLE ibf_macro (
  macro_id smallint(3) NOT NULL auto_increment,
  macro_value varchar(200) default NULL,
  macro_replace text,
  can_remove tinyint(1) default '0',
  macro_set smallint(3) default NULL,
  PRIMARY KEY  (macro_id),
  KEY macro_set (macro_set)
);

CREATE TABLE ibf_macro_name (
  set_id smallint(3) NOT NULL default '0',
  set_name varchar(200) default NULL,
  PRIMARY KEY  (set_id)
);

CREATE TABLE ibf_member_extra (
  id mediumint(8) NOT NULL default '0',
  notes text,
  links text,
  bio text,
  ta_size char(3) default NULL,
  photo_type varchar(10) default '',
  photo_location varchar(255) default '',
  photo_dimensions varchar(200) default '',
  PRIMARY KEY  (id)
);

CREATE TABLE ibf_members (
  id mediumint(8) NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  mgroup smallint(3) NOT NULL default '0',
  password varchar(32) NOT NULL default '',
  email varchar(60) NOT NULL default '',
  joined int(10) NOT NULL default '0',
  ip_address varchar(16) NOT NULL default '',
  avatar varchar(128) default NULL,
  avatar_size varchar(9) default NULL,
  posts mediumint(7) default '0',
  aim_name varchar(40) default NULL,
  icq_number varchar(40) default NULL,
  location varchar(128) default NULL,
  signature text,
  website varchar(70) default NULL,
  yahoo varchar(32) default NULL,
  title varchar(64) default NULL,
  allow_admin_mails tinyint(1) default NULL,
  time_offset varchar(10) default NULL,
  interests text,
  hide_email varchar(8) default NULL,
  email_pm tinyint(1) default NULL,
  email_full tinyint(1) default NULL,
  skin smallint(5) default NULL,
  warn_level int(10) default NULL,
  warn_lastwarn int(10) NOT NULL default '0',
  language varchar(32) default NULL,
  msnname varchar(64) default NULL,
  last_post int(10) default NULL,
  restrict_post varchar(100) NOT NULL default '0',
  view_sigs tinyint(1) default '1',
  view_img tinyint(1) default '1',
  view_avs tinyint(1) default '1',
  view_pop tinyint(1) default '1',
  bday_day int(2) default NULL,
  bday_month int(2) default NULL,
  bday_year int(4) default NULL,
  new_msg tinyint(2) default NULL,
  msg_from_id mediumint(8) default NULL,
  msg_msg_id int(10) default NULL,
  msg_total smallint(5) default NULL,
  vdirs text,
  show_popup tinyint(1) default NULL,
  misc varchar(128) default NULL,
  last_visit int(10) default '0',
  last_activity int(10) default '0',
  dst_in_use tinyint(1) default '0',
  view_prefs varchar(64) default '-1&-1',
  coppa_user tinyint(1) default '0',
  mod_posts varchar(100) NOT NULL default '0',
  auto_track tinyint(1) default '0',
  org_perm_id varchar(255) default '',
  org_supmod tinyint(1) default '0',
  integ_msg varchar(250) default '',
  temp_ban varchar(100) default NULL,
  sub_end int(10) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY name (name),
  KEY mgroup (mgroup),
  KEY bday_day (bday_day),
  KEY bday_month (bday_month)
);

CREATE TABLE ibf_messages (
  msg_id int(10) NOT NULL auto_increment,
  msg_date int(10) default NULL,
  read_state tinyint(1) default NULL,
  title varchar(128) default NULL,
  message text,
  from_id mediumint(8) NOT NULL default '0',
  vid varchar(32) default NULL,
  member_id mediumint(8) NOT NULL default '0',
  recipient_id mediumint(8) NOT NULL default '0',
  attach_type tinyint(128) default NULL,
  attach_file tinyint(128) default NULL,
  cc_users text,
  tracking tinyint(1) default '0',
  read_date int(10) default NULL,
  PRIMARY KEY  (msg_id),
  KEY member_id (member_id),
  KEY vid (vid)
);

CREATE TABLE ibf_moderator_logs (
  id int(10) NOT NULL auto_increment,
  forum_id int(5) default '0',
  topic_id int(10) NOT NULL default '0',
  post_id int(10) default NULL,
  member_id mediumint(8) NOT NULL default '0',
  member_name varchar(32) NOT NULL default '',
  ip_address varchar(16) NOT NULL default '0',
  http_referer varchar(255) default NULL,
  ctime int(10) default NULL,
  topic_title varchar(128) default NULL,
  action varchar(128) default NULL,
  query_string varchar(128) default NULL,
  PRIMARY KEY  (id)
);

CREATE TABLE ibf_moderators (
  mid mediumint(8) NOT NULL auto_increment,
  forum_id int(5) NOT NULL default '0',
  member_name varchar(32) NOT NULL default '',
  member_id mediumint(8) NOT NULL default '0',
  edit_post tinyint(1) default NULL,
  edit_topic tinyint(1) default NULL,
  delete_post tinyint(1) default NULL,
  delete_topic tinyint(1) default NULL,
  view_ip tinyint(1) default NULL,
  open_topic tinyint(1) default NULL,
  close_topic tinyint(1) default NULL,
  mass_move tinyint(1) default NULL,
  mass_prune tinyint(1) default NULL,
  move_topic tinyint(1) default NULL,
  pin_topic tinyint(1) default NULL,
  unpin_topic tinyint(1) default NULL,
  post_q tinyint(1) default NULL,
  topic_q tinyint(1) default NULL,
  allow_warn tinyint(1) default NULL,
  edit_user tinyint(1) NOT NULL default '0',
  is_group tinyint(1) default '0',
  group_id smallint(3) default NULL,
  group_name varchar(200) default NULL,
  split_merge tinyint(1) default '0',
  can_mm tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (mid),
  KEY forum_id (forum_id),
  KEY group_id (group_id),
  KEY member_id (member_id)
);

CREATE TABLE ibf_pfields_content (
  member_id mediumint(8) NOT NULL default '0',
  updated int(10) default '0',
  PRIMARY KEY  (member_id)
);

CREATE TABLE ibf_pfields_data (
  fid smallint(5) NOT NULL auto_increment,
  ftitle varchar(200) NOT NULL default '',
  fdesc varchar(250) default '',
  fcontent text,
  ftype varchar(250) default 'text',
  freq tinyint(1) default '0',
  fhide tinyint(1) default '0',
  fmaxinput smallint(6) default '250',
  fedit tinyint(1) default '1',
  forder smallint(6) default '1',
  fshowreg tinyint(1) default '0',
  PRIMARY KEY  (fid)
);

CREATE TABLE ibf_polls (
  pid mediumint(8) NOT NULL auto_increment,
  tid int(10) NOT NULL default '0',
  start_date int(10) default NULL,
  choices text,
  starter_id mediumint(8) NOT NULL default '0',
  votes smallint(5) NOT NULL default '0',
  forum_id smallint(5) NOT NULL default '0',
  poll_question varchar(255) default NULL,
  PRIMARY KEY  (pid)
);


CREATE TABLE ibf_posts (
  pid int(10) NOT NULL auto_increment,
  append_edit tinyint(1) default '0',
  edit_time int(10) default NULL,
  author_id mediumint(8) NOT NULL default '0',
  author_name varchar(32) default NULL,
  use_sig tinyint(1) NOT NULL default '0',
  use_emo tinyint(1) NOT NULL default '0',
  ip_address varchar(16) NOT NULL default '',
  post_date int(10) default NULL,
  icon_id smallint(3) default NULL,
  post text,
  queued tinyint(1) default NULL,
  topic_id int(10) NOT NULL default '0',
  forum_id smallint(5) NOT NULL default '0',
  attach_id varchar(64) default NULL,
  attach_hits int(10) default NULL,
  attach_type varchar(128) default NULL,
  attach_file varchar(255) default NULL,
  post_title varchar(255) default NULL,
  new_topic tinyint(1) default '0',
  edit_name varchar(255) default NULL,
  PRIMARY KEY  (pid),
  KEY topic_id (topic_id,author_id),
  KEY author_id (author_id),
  KEY forum_id (forum_id,post_date),
  FULLTEXT KEY post (post)
);


CREATE TABLE ibf_reg_antispam (
  regid varchar(32) NOT NULL default '',
  regcode varchar(8) NOT NULL default '',
  ip_address varchar(32) default NULL,
  ctime int(10) default NULL,
  PRIMARY KEY  (regid)
);

CREATE TABLE ibf_search_results (
  id varchar(32) NOT NULL default '',
  topic_id text NOT NULL,
  search_date int(12) NOT NULL default '0',
  topic_max int(3) NOT NULL default '0',
  sort_key varchar(32) NOT NULL default 'last_post',
  sort_order varchar(4) NOT NULL default 'desc',
  member_id mediumint(10) default '0',
  ip_address varchar(64) default NULL,
  post_id text,
  post_max int(10) NOT NULL default '0',
  query_cache text
);

CREATE TABLE ibf_sessions (
  id varchar(32) NOT NULL default '0',
  member_name varchar(64) default NULL,
  member_id mediumint(8) NOT NULL default '0',
  ip_address varchar(16) default NULL,
  browser varchar(64) default NULL,
  running_time int(10) default NULL,
  login_type tinyint(1) default NULL,
  location varchar(40) default NULL,
  member_group smallint(3) default NULL,
  in_forum smallint(5) NOT NULL default '0',
  in_topic int(10) default NULL,
  PRIMARY KEY  (id),
  KEY in_topic (in_topic),
  KEY in_forum (in_forum)
);

CREATE TABLE ibf_skin_templates (
  suid int(10) NOT NULL auto_increment,
  set_id int(10) NOT NULL default '0',
  group_name varchar(255) NOT NULL default '',
  section_content mediumtext,
  func_name varchar(255) default NULL,
  func_data text,
  updated int(10) default NULL,
  can_remove tinyint(4) default '0',
  PRIMARY KEY  (suid)
);

CREATE TABLE ibf_skins (
  uid int(10) NOT NULL auto_increment,
  sname varchar(100) NOT NULL default '',
  sid int(10) NOT NULL default '0',
  set_id int(5) NOT NULL default '0',
  tmpl_id int(10) NOT NULL default '0',
  macro_id int(10) NOT NULL default '1',
  css_id int(10) NOT NULL default '1',
  img_dir varchar(200) default '1',
  tbl_width varchar(250) default NULL,
  tbl_border varchar(250) default NULL,
  hidden tinyint(1) NOT NULL default '0',
  default_set tinyint(1) NOT NULL default '0',
  css_method varchar(100) default 'inline',
  PRIMARY KEY  (uid),
  KEY tmpl_id (tmpl_id),
  KEY css_id (css_id)
);

CREATE TABLE ibf_spider_logs (
  sid int(10) NOT NULL auto_increment,
  bot varchar(255) NOT NULL default '',
  query_string text NOT NULL,
  entry_date int(10) NOT NULL default '0',
  ip_address varchar(16) NOT NULL default '',
  PRIMARY KEY  (sid)
);

CREATE TABLE ibf_stats (
  TOTAL_REPLIES int(10) NOT NULL default '0',
  TOTAL_TOPICS int(10) NOT NULL default '0',
  LAST_MEM_NAME varchar(32) default NULL,
  LAST_MEM_ID mediumint(8) NOT NULL default '0',
  MOST_DATE int(10) default NULL,
  MOST_COUNT int(10) default '0',
  MEM_COUNT mediumint(8) NOT NULL default '0'
);

CREATE TABLE ibf_subscriptions (
 sub_id smallint(5) NOT NULL auto_increment,
 sub_title varchar(250) NOT NULL default '',
 sub_desc text,
 sub_new_group mediumint(8) NOT NULL default 0,
 sub_length smallint(5) NOT NULL default '1',
 sub_unit varchar(2) NOT NULL default 'm',
 sub_cost decimal(10,2) NOT NULL default '0.00',
 sub_run_module varchar(250) NOT NULL default '',
 PRIMARY KEY (sub_id)
) TYPE=MyISAM;

CREATE TABLE ibf_subscription_extra (
 subextra_id smallint(5) NOT NULL auto_increment,
 subextra_sub_id smallint(5) NOT NULL default '0',
 subextra_method_id smallint(5) NOT NULL default '0',
 subextra_product_id varchar(250) NOT NULL default '0',
 subextra_can_upgrade tinyint(1) NOT NULL default '0',
 subextra_recurring tinyint(1) NOT NULL default '0',
 subextra_custom_1 text,
 subextra_custom_2 text,
 subextra_custom_3 text,
 subextra_custom_4 text,
 subextra_custom_5 text,
 PRIMARY KEY(subextra_id)
) TYPE=MyISAM;


CREATE TABLE ibf_subscription_trans (
 subtrans_id int(10) NOT NULL auto_increment,
 subtrans_sub_id smallint(5) NOT NULL default '0',
 subtrans_member_id mediumint(8) NOT NULL default '0',
 subtrans_old_group smallint(5) NOT NULL default '0',
 subtrans_paid decimal(10,2) NOT NULL default '0.00',
 subtrans_cumulative decimal(10,2) NOT NULL default '0.00',
 subtrans_method varchar(20) NOT NULL default '',
 subtrans_start_date int(11) NOT NULL default '0',
 subtrans_end_date int(11) NOT NULL default '0',
 subtrans_state varchar(200) NOT NULL default '',
 subtrans_trxid varchar(200) NOT NULL default '',
 subtrans_subscrid varchar(200) NOT NULL default '',
 subtrans_currency varchar(10) NOT NULL default 'USD',
 PRIMARY KEY (subtrans_id)
) TYPE=MyISAM;

CREATE TABLE ibf_subscription_logs (
 sublog_id int(10) NOT NULL auto_increment,
 sublog_date int(10) NOT NULL default '',
 sublog_member_id mediumint(8) NOT NULL default '0',
 sublog_transid int(10) NOT NULL default '',
 sublog_ipaddress varchar(16) NOT NULL default '',
 sublog_data text,
 sublog_postdata text,
 PRIMARY KEY (sublog_id)
) TYPE=MyISAM;

CREATE TABLE ibf_subscription_methods (
 submethod_id smallint(5) NOT NULL auto_increment,
 submethod_title varchar(250) NOT NULL default '',
 submethod_name varchar(20) NOT NULL default '',
 submethod_email varchar(250) NOT NULL default '',
 submethod_sid text,
 submethod_custom_1 text,
 submethod_custom_2 text,
 submethod_custom_3 text,
 subemthod_custom_4 text,
 submethod_custom_5 text,
 submethod_is_cc tinyint(1) NOT NULL default '0',
 submethod_is_auto tinyint(1) NOT NULL default '0',
 submethod_desc text,
 submethod_logo text,
 submethod_active tinyint(1) NOT NULL default '',
 submethod_use_currency varchar(10) NOT NULL default 'USD',
 PRIMARY KEY (submethod_id)
) TYPE=MyISAM;

CREATE TABLE ibf_subscription_currency (
 subcurrency_code varchar(10) NOT NULL,
 subcurrency_desc varchar(250) NOT NULL default '',
 subcurrency_exchange decimal(10, 8) NOT NULL,
 subcurrency_default tinyint(1) NOT NULL default '0',
 PRIMARY KEY(subcurrency_code)
) TYPE=MyISAM;
	
	
	
CREATE TABLE ibf_templates (
  tmid int(10) NOT NULL auto_increment,
  template mediumtext,
  name varchar(128) default NULL,
  PRIMARY KEY  (tmid)
);

CREATE TABLE ibf_titles (
  id smallint(5) NOT NULL auto_increment,
  posts int(10) default NULL,
  title varchar(128) default NULL,
  pips varchar(128) default NULL,
  PRIMARY KEY  (id)
);

CREATE TABLE ibf_tmpl_names (
  skid int(10) NOT NULL auto_increment,
  skname varchar(60) NOT NULL default 'Invision Board',
  author varchar(250) default '',
  email varchar(250) default '',
  url varchar(250) default '',
  PRIMARY KEY  (skid)
);

CREATE TABLE ibf_topic_mmod (
  mm_id smallint(5) NOT NULL auto_increment,
  mm_title varchar(250) NOT NULL default '',
  mm_enabled tinyint(1) NOT NULL default '0',
  topic_state varchar(10) NOT NULL default 'leave',
  topic_pin varchar(10) NOT NULL default 'leave',
  topic_move smallint(5) NOT NULL default '0',
  topic_move_link tinyint(1) NOT NULL default '0',
  topic_title_st varchar(250) NOT NULL default '',
  topic_title_end varchar(250) NOT NULL default '',
  topic_reply tinyint(1) NOT NULL default '0',
  topic_reply_content text NOT NULL,
  topic_reply_postcount tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (mm_id)
);

CREATE TABLE ibf_topics (
  tid int(10) NOT NULL auto_increment,
  title varchar(250) NOT NULL default '',
  description varchar(70) default NULL,
  state varchar(8) default NULL,
  posts int(10) default NULL,
  starter_id mediumint(8) NOT NULL default '0',
  start_date int(10) default NULL,
  last_poster_id mediumint(8) NOT NULL default '0',
  last_post int(10) default NULL,
  icon_id tinyint(2) default NULL,
  starter_name varchar(32) default NULL,
  last_poster_name varchar(32) default NULL,
  poll_state varchar(8) default NULL,
  last_vote int(10) default NULL,
  views int(10) default NULL,
  forum_id smallint(5) NOT NULL default '0',
  approved tinyint(1) default NULL,
  author_mode tinyint(1) default NULL,
  pinned tinyint(1) default NULL,
  moved_to varchar(64) default NULL,
  rating text,
  total_votes int(5) NOT NULL default '0',
  PRIMARY KEY  (tid),
  KEY last_post (last_post),
  KEY forum_id (forum_id,approved,pinned),
  FULLTEXT KEY title (title)
);

CREATE TABLE ibf_tracker (
  trid mediumint(8) NOT NULL auto_increment,
  member_id mediumint(8) NOT NULL default '0',
  topic_id bigint(20) NOT NULL default '0',
  start_date int(10) default NULL,
  last_sent int(10) NOT NULL default '0',
  PRIMARY KEY  (trid)
);

CREATE TABLE ibf_validating (
  vid varchar(32) NOT NULL default '',
  member_id mediumint(8) NOT NULL default '0',
  real_group smallint(3) NOT NULL default '0',
  temp_group smallint(3) NOT NULL default '0',
  entry_date int(10) NOT NULL default '0',
  coppa_user tinyint(1) NOT NULL default '0',
  lost_pass tinyint(1) NOT NULL default '0',
  new_reg tinyint(1) NOT NULL default '0',
  email_chg tinyint(1) NOT NULL default '0',
  ip_address varchar(16) NOT NULL default '0',
  PRIMARY KEY  (vid),
  KEY new_reg (new_reg)
);

CREATE TABLE ibf_voters (
  vid int(10) NOT NULL auto_increment,
  ip_address varchar(16) NOT NULL default '',
  vote_date int(10) NOT NULL default '0',
  tid int(10) NOT NULL default '0',
  member_id varchar(32) default NULL,
  forum_id smallint(5) NOT NULL default '0',
  PRIMARY KEY  (vid)
);

CREATE TABLE ibf_warn_logs (
  wlog_id int(10) NOT NULL auto_increment,
  wlog_mid mediumint(8) NOT NULL default '0',
  wlog_notes text NOT NULL,
  wlog_contact varchar(250) NOT NULL default 'none',
  wlog_contact_content text NOT NULL,
  wlog_date int(10) NOT NULL default '0',
  wlog_type varchar(6) NOT NULL default 'pos',
  wlog_addedby mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (wlog_id)
);