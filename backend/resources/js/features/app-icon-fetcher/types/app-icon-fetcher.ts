export type StoreIconResult = {
    found: boolean;
    icon_url: string | null;
    message: string | null;
};

export type NormalizedAppInput = {
    original: string;
    type: string;
    bundle_id: string | null;
    apple_app_id: string | null;
};

export type FetchAppIconsData = {
    input: NormalizedAppInput;
    icons: {
        apple: StoreIconResult;
        google: StoreIconResult;
    };
};

export type FetchAppIconsResponse = {
    data: FetchAppIconsData;
};
