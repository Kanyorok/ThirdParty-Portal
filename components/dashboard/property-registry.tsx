'use client'

import { fetchProperties } from '@/lib/api'
import { Property } from '@/types/property'
import { useState, useEffect } from 'react'
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '@/components/common/card'
import { Skeleton } from '@/components/common/skeleton'
import { Alert, AlertDescription, AlertTitle } from '@/components/common/alert'
import { Badge } from '@/components/common/badge'
import { AlertCircle, MapPin, User, Landmark, Package, ArrowRight } from 'lucide-react'
import { motion, AnimatePresence } from 'framer-motion'
import Image from 'next/image'

interface DetailItemProps {
    icon: React.ReactNode;
    label: string;
    value: string;
}

const DetailItem: React.FC<DetailItemProps> = ({ icon, label, value }) => (
    <div className="flex items-start text-sm py-1">
        <div className="text-gray-500 mr-3 mt-1 flex-shrink-0">
            {icon}
        </div>
        <div className="flex flex-col">
            <span className="font-medium text-xs text-gray-400 uppercase tracking-wider">{label}</span>
            <span className="text-base font-semibold text-gray-800 break-words">{value}</span>
        </div>
    </div>
)

interface PropertyCardProps {
    property: Property;
    index: number;
}

const PropertyCard: React.FC<PropertyCardProps> = ({ property, index }) => {

    const getCategoryVariant = (categoryId: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
        switch (categoryId) {
            case '1': return 'default';
            case '2': return 'secondary';
            case '3': return 'outline';
            default: return 'outline';
        }
    }

    return (
        <motion.div
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5, delay: index * 0.04 }}
            whileHover={{ scale: 1.01 }}
            className="h-full group"
        >
            <Card className="border-2 border-gray-100 transition-all duration-300 h-full flex flex-col overflow-hidden hover:border-blue-500">
                <div className="relative w-full h-40 bg-gray-100 overflow-hidden">
                    <Image
                        src={`https://picsum.photos/seed/${property.id}/600/400`}
                        alt={`Image of ${property.propertyName}`}
                        layout="fill"
                        objectFit="cover"
                        className="transition-transform duration-500 group-hover:scale-105"
                    />
                    <div className="absolute top-0 right-0 bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-bl-lg">
                        CODE: {property.propertyCode}
                    </div>
                </div>

                <CardHeader className="pb-3 px-6 pt-4">
                    <div className="flex items-center space-x-3 mb-2">
                        <Landmark className="h-6 w-6 text-blue-600 flex-shrink-0" />
                        <CardTitle className="text-xl font-extrabold text-gray-900 leading-snug">
                            {property.propertyName}
                        </CardTitle>
                    </div>
                    <div className="pl-9">
                        <Badge variant={getCategoryVariant(property.category)} className="text-xs font-semibold tracking-wide">
                            {property.type.propertyTypeName}
                        </Badge>
                    </div>
                </CardHeader>

                <CardContent className="grid gap-3 p-6 flex-grow border-t border-dashed border-gray-100 mt-2">
                    <DetailItem
                        icon={<User className="h-4 w-4" />}
                        label="Owned By"
                        value={property.owner}
                    />
                    <DetailItem
                        icon={<MapPin className="h-4 w-4" />}
                        label="Location"
                        value={property.address}
                    />
                </CardContent>

                <CardFooter className="p-4 border-t border-gray-100 flex justify-between items-center text-sm text-blue-600 font-bold bg-white transition-colors group-hover:bg-blue-50">
                    <span>Explore Property Details</span>
                    <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                </CardFooter>
            </Card>
        </motion.div>
    )
}

const PropertyList: React.FC = () => {
    const [properties, setProperties] = useState<Property[]>([])
    const [isLoading, setIsLoading] = useState(true)
    const [error, setError] = useState<string | null>(null)

    useEffect(() => {
        const loadProperties = async () => {
            try {
                const data = await fetchProperties()
                setProperties(data)
                setError(null)
            } catch (err) {
                if (err instanceof Error) {
                    setError(err.message)
                } else {
                    setError('An unexpected error occurred.')
                }
            } finally {
                setIsLoading(false)
            }
        }
        loadProperties()
    }, [])

    if (error) {
        return (
            <div className="p-8 max-w-4xl mx-auto">
                <Alert variant="destructive">
                    <AlertCircle className="h-4 w-4" />
                    <AlertTitle>API Error</AlertTitle>
                    <AlertDescription>
                        {error}
                    </AlertDescription>
                </Alert>
            </div>
        )
    }

    return (
        <div className="flex-1 min-h-screen pt-4 pb-12">
            <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 className="text-4xl font-extrabold mb-8 tracking-tight text-gray-900 border-b border-gray-200 pb-4">
                    Property Asset <span className="text-blue-600">Inventory</span>
                </h1>

                <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                    <AnimatePresence>
                        {isLoading ? (
                            Array.from({ length: 10 }).map((_, i) => (
                                <motion.div
                                    key={i}
                                    initial={{ opacity: 0 }}
                                    animate={{ opacity: 1 }}
                                    exit={{ opacity: 0 }}
                                    transition={{ duration: 0.3 }}
                                >
                                    <Card className="border-2 border-gray-100 h-[380px] flex flex-col">
                                        <Skeleton className="w-full h-40 rounded-t-lg" />
                                        <CardHeader className="pb-3 px-6 pt-4"><Skeleton className="h-4 w-1/4 mb-2" /><Skeleton className="h-7 w-4/5" /></CardHeader>
                                        <CardContent className="grid gap-3 p-6 flex-grow">
                                            <Skeleton className="h-4 w-full" />
                                            <Skeleton className="h-4 w-5/6" />
                                        </CardContent>
                                    </Card>
                                </motion.div>
                            ))
                        ) : properties.length === 0 ? (
                            <div className="col-span-full">
                                <Alert>
                                    <Package className="h-4 w-4" />
                                    <AlertTitle>No Properties Found</AlertTitle>
                                    <AlertDescription>The property inventory is currently empty. Use the management tools to add new assets.</AlertDescription>
                                </Alert>
                            </div>
                        ) : (
                            properties.map((property, index) => (
                                <PropertyCard key={property.id} property={property} index={index} />
                            ))
                        )}
                    </AnimatePresence>
                </div>
            </div>
        </div>
    )
}

export default PropertyList