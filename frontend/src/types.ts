export interface RouteRequest {
    fromStationId: string;
    toStationId: string;
    analyticCode: string;
}

export interface Route {
    id: string;
    fromStationId: string;
    toStationId: string;
    analyticCode: string;
    distanceKm: number;
    path: string[];
    createdAt: string;
}

export interface AnalyticDistance {
    analyticCode: string;
    totalDistanceKm: number;
    periodStart?: string;
    periodEnd?: string;
    group?: string;
}

export interface AnalyticDistanceList {
    from?: string | null;
    to?: string | null;
    groupBy?: 'day' | 'month' | 'year' | 'none';
    items: AnalyticDistance[];
}

export interface ApiError {
    code?: string;
    message: string;
    details?: string[];
}
