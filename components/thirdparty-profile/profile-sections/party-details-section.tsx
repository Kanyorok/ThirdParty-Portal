"use client"

import { Card } from "@/components/common/card"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/common/table"
import { Button } from "@/components/common/button"
import { Eye, Edit, Trash2, Plus } from "lucide-react"

interface BankDetail {
    id: string
    name: string
    dated: string
    actions: string
}

interface User {
    id: string
    name: string
    email: string
    role: string
    status: string
}

interface PartyDetailsSectionProps {
    bankDetails?: BankDetail[]
    users?: User[]
}

export function PartyDetailsSection({ bankDetails = [], users = [] }: PartyDetailsSectionProps) {
    return (
        <div className="space-y-8">
            {/* Bank Details Card */}
            <Card className="border-border/50 shadow-sm hover:shadow-md transition-shadow">
                <div className="p-8">
                    <div className="flex items-center justify-between mb-6">
                        <h3 className="text-lg font-bold text-foreground">Bank Details</h3>
                        <Button size="sm" className="gap-2 bg-transparent" variant="outline">
                            <Plus className="w-4 h-4" />
                            Add Bank
                        </Button>
                    </div>

                    {bankDetails.length > 0 ? (
                        <div className="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow className="border-border/50 hover:bg-transparent">
                                        <TableHead className="text-xs font-semibold text-muted-foreground uppercase">#</TableHead>
                                        <TableHead className="text-xs font-semibold text-muted-foreground uppercase">Name</TableHead>
                                        <TableHead className="text-xs font-semibold text-muted-foreground uppercase">Dated</TableHead>
                                        <TableHead className="text-xs font-semibold text-muted-foreground uppercase">Action</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {bankDetails.map((detail, idx) => (
                                        <TableRow key={detail.id} className="border-border/50 hover:bg-card/50 transition-colors">
                                            <TableCell className="font-medium text-foreground">{idx + 1}</TableCell>
                                            <TableCell className="text-foreground">{detail.name}</TableCell>
                                            <TableCell className="text-muted-foreground">{detail.dated}</TableCell>
                                            <TableCell>
                                                <div className="flex gap-2">
                                                    <Button size="sm" variant="ghost" className="h-8 w-8 p-0">
                                                        <Eye className="w-4 h-4 text-muted-foreground hover:text-primary" />
                                                    </Button>
                                                    <Button size="sm" variant="ghost" className="h-8 w-8 p-0">
                                                        <Edit className="w-4 h-4 text-muted-foreground hover:text-primary" />
                                                    </Button>
                                                    <Button size="sm" variant="ghost" className="h-8 w-8 p-0">
                                                        <Trash2 className="w-4 h-4 text-muted-foreground hover:text-destructive" />
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                    ) : (
                        <div className="text-center py-12">
                            <p className="text-muted-foreground text-sm">No bank details added yet</p>
                            <Button variant="outline" size="sm" className="mt-4 gap-2 bg-transparent">
                                <Plus className="w-4 h-4" />
                                Add First Bank Detail
                            </Button>
                        </div>
                    )}
                </div>
            </Card>

            {/* Users Card */}
            <Card className="border-border/50 shadow-sm hover:shadow-md transition-shadow">
                <div className="p-8">
                    <div className="flex items-center justify-between mb-6">
                        <h3 className="text-lg font-bold text-foreground">Users</h3>
                        <Button size="sm" className="gap-2">
                            <Plus className="w-4 h-4" />
                            Add User
                        </Button>
                    </div>

                    {users.length > 0 ? (
                        <div className="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow className="border-border/50 hover:bg-transparent">
                                        <TableHead className="text-xs font-semibold text-muted-foreground uppercase">Name</TableHead>
                                        <TableHead className="text-xs font-semibold text-muted-foreground uppercase">Email</TableHead>
                                        <TableHead className="text-xs font-semibold text-muted-foreground uppercase">Role</TableHead>
                                        <TableHead className="text-xs font-semibold text-muted-foreground uppercase">Status</TableHead>
                                        <TableHead className="text-xs font-semibold text-muted-foreground uppercase">Action</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {users.map((user) => (
                                        <TableRow key={user.id} className="border-border/50 hover:bg-card/50 transition-colors">
                                            <TableCell className="font-medium text-foreground">{user.name}</TableCell>
                                            <TableCell className="text-muted-foreground">{user.email}</TableCell>
                                            <TableCell className="text-foreground text-sm">{user.role}</TableCell>
                                            <TableCell>
                                                <span className="inline-flex items-center rounded-md bg-success/10 px-2 py-1 text-xs font-medium text-success">
                                                    {user.status}
                                                </span>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex gap-2">
                                                    <Button size="sm" variant="ghost" className="h-8 w-8 p-0">
                                                        <Edit className="w-4 h-4 text-muted-foreground hover:text-primary" />
                                                    </Button>
                                                    <Button size="sm" variant="ghost" className="h-8 w-8 p-0">
                                                        <Trash2 className="w-4 h-4 text-muted-foreground hover:text-destructive" />
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                    ) : (
                        <div className="text-center py-12">
                            <p className="text-muted-foreground text-sm">No users added yet</p>
                            <Button variant="outline" size="sm" className="mt-4 gap-2 bg-transparent">
                                <Plus className="w-4 h-4" />
                                Invite First User
                            </Button>
                        </div>
                    )}
                </div>
            </Card>
        </div>
    )
}
