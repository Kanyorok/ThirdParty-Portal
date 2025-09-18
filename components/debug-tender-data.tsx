"use client";

import React, { useState } from "react";
import { Button } from "@/components/common/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/common/card";

export default function DebugTenderData() {
  const [debugData, setDebugData] = useState<any>(null);
  const [isLoading, setIsLoading] = useState(false);

  const handleDebug = async () => {
    setIsLoading(true);
    try {
      const response = await fetch('/api/debug-tender-data');
      const data = await response.json();
      setDebugData(data);
      console.log('🔍 Debug Data:', data);
    } catch (error) {
      console.error('Debug failed:', error);
      setDebugData({ error: 'Failed to fetch debug data' });
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <Card className="m-4">
      <CardHeader>
        <CardTitle>🔧 Debug Tender Data</CardTitle>
      </CardHeader>
      <CardContent>
        <Button 
          onClick={handleDebug} 
          disabled={isLoading}
          className="mb-4"
        >
          {isLoading ? 'Debugging...' : 'Debug Backend Data'}
        </Button>
        
        {debugData && (
          <div className="space-y-4">
            <div>
              <h4 className="font-semibold">User Info:</h4>
              <pre className="text-xs bg-gray-100 p-2 rounded overflow-auto">
                {JSON.stringify(debugData.user, null, 2)}
              </pre>
            </div>
            
            <div>
              <h4 className="font-semibold">Tenders Data:</h4>
              <pre className="text-xs bg-gray-100 p-2 rounded overflow-auto max-h-40">
                {JSON.stringify(debugData.results?.tenders, null, 2)}
              </pre>
            </div>
            
            <div>
              <h4 className="font-semibold">Invitations Data:</h4>
              <pre className="text-xs bg-gray-100 p-2 rounded overflow-auto max-h-40">
                {JSON.stringify(debugData.results?.invitations, null, 2)}
              </pre>
            </div>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
