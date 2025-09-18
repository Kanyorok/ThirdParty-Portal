"use client";

import React, { useState } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/common/card";
import { Button } from "@/components/common/button";

export default function ApiDebugTest() {
  const [results, setResults] = useState<any>(null);
  const [loading, setLoading] = useState(false);

  const testTendersApi = async () => {
    setLoading(true);
    try {
      const response = await fetch('http://localhost:8000/api/tenders', {
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        }
      });
      const data = await response.json();
      setResults({
        endpoint: 'Tenders API',
        status: response.status,
        data: data,
        success: response.ok
      });
    } catch (error) {
      setResults({
        endpoint: 'Tenders API',
        error: error instanceof Error ? error.message : 'Unknown error',
        success: false
      });
    } finally {
      setLoading(false);
    }
  };

  const testInvitationsApi = async () => {
    setLoading(true);
    try {
      const response = await fetch('http://localhost:8000/api/tender-invitations?third_party_id=1', {
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        }
      });
      const data = await response.json();
      setResults({
        endpoint: 'Invitations API',
        status: response.status,
        data: data,
        success: response.ok
      });
    } catch (error) {
      setResults({
        endpoint: 'Invitations API',
        error: error instanceof Error ? error.message : 'Unknown error',
        success: false
      });
    } finally {
      setLoading(false);
    }
  };

  const testDebugApi = async () => {
    setLoading(true);
    try {
      const response = await fetch('http://localhost:8000/api/debug/tender-invitations?third_party_id=1', {
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        }
      });
      const data = await response.json();
      setResults({
        endpoint: 'Debug API',
        status: response.status,
        data: data,
        success: response.ok
      });
    } catch (error) {
      setResults({
        endpoint: 'Debug API',
        error: error instanceof Error ? error.message : 'Unknown error',
        success: false
      });
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-4">
      <Card>
        <CardHeader>
          <CardTitle>🔧 API Debug Test</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="flex gap-2">
            <Button onClick={testTendersApi} disabled={loading}>
              Test Tenders API
            </Button>
            <Button onClick={testInvitationsApi} disabled={loading}>
              Test Invitations API  
            </Button>
            <Button onClick={testDebugApi} disabled={loading}>
              Test Debug API
            </Button>
          </div>

          {loading && (
            <div className="flex items-center gap-2">
              <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
              <span>Loading...</span>
            </div>
          )}

          {results && (
            <Card className={results.success ? "border-green-200 bg-green-50" : "border-red-200 bg-red-50"}>
              <CardHeader>
                <CardTitle className="text-lg">
                  {results.endpoint} - Status: {results.status || 'Error'}
                  {results.success ? " ✅" : " ❌"}
                </CardTitle>
              </CardHeader>
              <CardContent>
                <pre className="text-xs overflow-auto max-h-96 bg-white p-4 rounded border">
                  {JSON.stringify(results, null, 2)}
                </pre>
              </CardContent>
            </Card>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
