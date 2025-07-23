<?php

/**
 *
 * @psalm-type DesignSetPost = array{
 * id: int,
 * name: string,
 * url: string,
 * tags: string[]
 * }
 *
 * @psalm-type DesignSetData = array{
 * enabled: bool,
 * name: string,
 * fullSiteImportEnabled: bool,
 * fullSiteImportThumbnailUrl: string,
 * posts: DesignSetPost[],
 * requiresPassword: bool,
 * passwordValid?: bool,
 * }
 *
 */
