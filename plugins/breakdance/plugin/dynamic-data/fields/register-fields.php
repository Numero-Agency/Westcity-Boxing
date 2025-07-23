<?php

namespace Breakdance\DynamicData;

$fields = [
    /** User */
    new CurrentUserEmail(),
    new CurrentUserName(),
    new CurrentUserWebsite(),
    new CurrentUserCustomField(),
    new CurrentUserCustomFieldImage(),
    new CurrentUserCustomFieldOembed(),
    new CurrentUserBio(),
    new CurrentUserAvatar(),
    new CurrentUserAvatarUrl(),

    /** Post */
    new PostTitle(),
    new PostTime(),
    new PostTerms(),
    new PostExcerpt(),
    new PostDate(),
    new PostCustomField(),
    new PostCustomFieldImage(),
    new PostCustomFieldOembed(),
    new PostContent(),
    new PostCommentsNumber(),
    new PostImageAttachment(),
    new PostImageAttachments(),
    new PostFeaturedImage(),
    new PostFeaturedImageURL(),
    new PostPermalink(),

    /** Term */
    new TermName(),
    new TermId(),
    new TermCount(),
    new TermPermalink(),
    new TermCustomField(),
    new TermDescription(),

    /** Archive */
    new ArchiveTitle(),
    new ArchiveDescription(),

    /** Featured Image */
    new FeaturedAlt(),
    new FeaturedCaption(),
    new FeaturedCustomField(),
    new FeaturedCustomFieldImage(),
    new FeaturedCustomFieldOembed(),
    new FeaturedTitle(),

    /** Author */
    new AuthorBio(),
    new AuthorCustomField(),
    new AuthorCustomFieldImage(),
    new AuthorCustomFieldOembed(),
    new AuthorEmail(),
    new AuthorName(),
    new AuthorWebsite(),
    new AuthorAvatar(),
    new AuthorAvatarUrl(),

    /** Site */
    new AdminEmail(),
    new HomeUrl(),
    new SiteRss(),
    new SiteTagline(),
    new SiteTitle(),
    new SiteUrl(),
    new SiteLogo(),
    new SiteLogoURL(),
    new SiteLogoutUrl(),

    /** Utility */
    new Date(),
    new Shortcode(),
    new PHPField(),

    /** Query */
    new UrlParameter(),
    new UtmTags(),
    new SearchQuery(),
];

foreach ($fields as $field) {
    DynamicDataController::getInstance()->registerField($field);
}
